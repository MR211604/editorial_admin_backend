<?php
namespace App\Http\Controllers;


use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exceptions\ServerErrorException;

/**
 * @resource Auth
 *
 * Endpoints de autenticación (login, renovación de token y logout).
 * Usa JWT en el header: `Authorization: Bearer {token}`.
 */
class AuthController extends Controller
{

  /**
   * Constructor (middleware)
   *
   * Aplica el middleware `auth:api` a todas las rutas, excepto `login`.
   */
  public function __construct()
  {
    $this->middleware('auth:api', ['except' => ['login']]);
  }

  /**
   * Iniciar sesión
   * 
   * Autentica a un usuario con sus credenciales y devuelve un token JWT.
   * El token debe ser incluido en el header Authorization como Bearer token para las siguientes peticiones.
   * 
   * **Parámetros del Body:**
   * - `token` (string, requerido): El token único del usuario (username). Ejemplo: `admin123`
   * - `password` (string, requerido): La contraseña del usuario. Ejemplo: `password123`
   * 
   * ```json
   * {
   *   "token": "admin123",
   *   "password": "mySecurePassword123"
   * }
   * ```
   * 
   * @response {
   *   "ok": true,
   *   "message": "Usuario conectado correctamente",
   *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODA4MFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTczMTY5NjAwMCwiZXhwIjoxNzMxNjk5NjAwLCJuYmYiOjE3MzE2OTYwMDAsImp0aSI6ImFiY2RlZjEyMzQ1IiwidXNlcl9pZCI6MX0",
   *   "user": {
   *     "id": 1,
   *     "name": "Juan Pérez",
   *     "email": "juan@example.com",
   *     "token": "admin123",
   *     "active": 1,
   *     "join_date": "2025-11-15 10:30:00",
   *     "last_login_date": "2025-11-15 10:30:00"
   *   },
   *   "rol": {
   *     "id": 1,
   *     "name": "Administrador"
   *   },
   *   "first_login": "2025-01-10 08:00:00",
   *   "last_login": "2025-11-14 15:45:00"
   * }
   * 
   * @response 401 {
   *   "error": "Credenciales incorrectas"
   * }
   * 
   * @response 400 {
   *   "error": "Usuario desactivado"
   * }
   */
  public function login(Request $request)
  {
    $credentials = $request->only("token", "password");

    try {

      if (!$token = JWTAuth::attempt($credentials)) {
        return response()->json(['error' => 'Credenciales incorrectas'], 401);
      }

      $user = Auth::user();

      if ($user->active === 0) {
        return response()->json(['error' => 'Usuario desactivado'], 400);
      }

      if (!$user->join_date) {
        $user->join_date = Carbon::now()->toDateTimeString();
      }
      $user->last_login_date = Carbon::now()->toDateTimeString();

      $user->save();

      // Obtener el usuario con sus roles para incluirlos en la respuesta
      $userQuery = User::with('userRoles')->where('idUser', $user->idUser)->first();

      return response()->json([
        'ok' => true,
        'message' => 'Usuario conectado correctamente',
        'token' => $token,
        'user' => $userQuery,
      ]);
    } catch (\Exception $e) {
      throw new ServerErrorException('No se pudo iniciar sesión', null, $e);
    }
  }

  /**
   * Renovar token
   * 
   * Renueva un token JWT existente antes de que expire completamente.
   * El usuario debe estar autenticado con un token válido para poder renovarlo.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere que incluyas tu token JWT actual en el header de autorización.
   * 
   * **Header requerido:**
   * - `Authorization`: Bearer {tu_token_actual}
   * 
   * ```bash
   * curl -X GET "http://localhost/api/auth/renew" \
   *   -H "Accept: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
   * ```
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Token renovado correctamente",
   *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODA4MFwvYXBpXC9hdXRoXC9yZW5ldyIsImlhdCI6MTczMTY5NjAwMCwiZXhwIjoxNzMxNjk5NjAwLCJuYmYiOjE3MzE2OTYwMDAsImp0aSI6ImFiY2RlZjEyMzQ1IiwidXNlcl9pZCI6MX0",
   *   "user": {
   *     "id": 1,
   *     "name": "Juan Pérez",
   *     "email": "juan@example.com",
   *     "token": "admin123",
   *     "active": 1
   *   }
   * }
   * 
   * @response 401 {
   *   "ok": false,
   *   "error": "Token no proporcionado",
   *   "message": "Se requiere un token de autorización"
   * }
   * 
   * @response 401 {
   *   "ok": false,
   *   "error": "Token expirado",
   *   "message": "El token ha expirado y no puede ser renovado"
   * }
   * 
   * @response 401 {
   *   "ok": false,
   *   "error": "Token inválido",
   *   "message": "El token proporcionado no es válido"
   * }
   */
  public function renew(Request $request)
  {
    try {
      $token = $request->bearerToken();

      if (!$token) {
        return response()->json([
          'ok' => false,
          'error' => 'Token no proporcionado',
          'message' => 'Se requiere un token de autorización'
        ], 401);
      }

      // Establecer el token en JWTAuth
      JWTAuth::setToken($token);

      // Verificar que el token es válido antes de renovarlo
      $user = JWTAuth::parseToken()->authenticate();

      if (!$user) {
        return response()->json([
          'ok' => false,
          'error' => 'Usuario no encontrado',
          'message' => 'El token no corresponde a un usuario válido'
        ], 401);
      }

      $newToken = JWTAuth::refresh();

      return response()->json([
        'ok' => true,
        'message' => 'Token renovado correctamente',
        'token' => $newToken,
        'user' => $user,
      ]);

    } catch (TokenExpiredException $e) {
      return response()->json([
        'ok' => false,
        'error' => 'Token expirado',
        'message' => 'El token ha expirado y no puede ser renovado'
      ], 401);

    } catch (TokenInvalidException $e) {
      return response()->json([
        'ok' => false,
        'error' => 'Token inválido',
        'message' => 'El token proporcionado no es válido'
      ], 401);

    } catch (JWTException $e) {
      return response()->json([
        'ok' => false,
        'error' => 'Error de token',
        'message' => 'Error al procesar el token'
      ], 500);

    } catch (\Exception $e) {
      throw new ServerErrorException('No se pudo renovar el token', null, $e);
    }
  }

  /**
   * Cerrar sesión
   * 
   * Invalida el token JWT actual del usuario, cerrando su sesión.
   * Después de cerrar sesión, el token ya no será válido para futuras peticiones.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere que incluyas tu token JWT actual en el header de autorización.
   * 
   * **Header requerido:**
   * - `Authorization`: Bearer {tu_token_actual}
   * 
   * ```bash
   * curl -X POST "http://localhost/api/auth/logout" \
   *   -H "Accept: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
   * ```
   * 
   * **Nota:** Una vez que cierres sesión, deberás volver a iniciar sesión para obtener un nuevo token.
   * 
   * @authenticated
   * 
   * @response {
   *   "message": "Usuario desconectado correctamente"
   * }
   * 
   * @response 401 {
   *   "message": "Unauthenticated"
   * }
   */
  public function logout()
  {
    JWTAuth::invalidate(JWTAuth::getToken());
    return response()->json(['message' => 'Usuario desconectado correctamente']);
  }
}
