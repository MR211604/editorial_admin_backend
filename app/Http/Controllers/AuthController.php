<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RenewTokenRequest;
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
   * Autentica con credenciales y devuelve un **JWT** (Bearer).
   * > Este endpoint **no** requiere autenticación previa.
   *
   * @group Auth
   * @bodyParam token string required Identificador de login. Example: admin@demo.com
   * @bodyParam password string required Contraseña del usuario. Example: secret123
   *
   * @response  {
   *   "ok": true,
   *   "message": "Usuario conectado correctamente",
   *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N.....",
   *   "user": {
   *       "id": 2,
   *       "name": "testnameupdate",
   *       "active": 1,
   *       "join_date": "2025-06-29 19:42:59",
   *       "last_login_date": "2025-10-23 10:34:31",
   *       "rol_id": 2,
   *       "token": "testnameupdate"
   *   },
   *   "first_login": null,
   *   "last_login": null
   * }
   * @response 401 { "error": "Credenciales incorrectas" }
   * @response 500 { "ok": false, "message": "No se pudo iniciar sesión" }
   */
  public function login(LoginRequest $request)
  {
    $credentials = $request->only(['token', 'password']);

    try {

      if (!$token = JWTAuth::attempt($credentials)) {
        return response()->json(['error' => 'Credenciales incorrectas'], 401);
      }

      $user = Auth::user();

      if (!$user->join_date) {
        $user->join_date = Carbon::now()->toDateTimeString();
      }
      $user->last_login_date = Carbon::now()->toDateTimeString();

      $user->save();

      return response()->json([
        'ok' => true,
        'message' => 'Usuario conectado correctamente',
        'token' => $token,
        'user' => $user,
        'first_login' => $user->first_login_at,
        'last_login' => $user->last_login_at
      ]);
    } catch (\Exception $e) {
      throw new ServerErrorException('No se pudo iniciar sesión', null, $e);
    }
  }

  /**
   * Renovar token
   *
   * Recibe el **Bearer Token** actual y devuelve un **nuevo token** válido.
   *
   * @group Auth
   * @authenticated
   * @header Authorization string required Token JWT actual en formato `Bearer {token}`.
   *
   * @response {
   *   "ok": true,
   *   "message": "Token renovado correctamente",
   *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N.....",
   *   "user": {
   *       "id": 2,
   *       "name": "testnameupdate",
   *       "active": 1,
   *       "join_date": "2025-06-29 19:42:59",
   *       "last_login_date": "2025-10-23 10:34:31",
   *       "rol_id": 2,
   *       "token": "testnameupdate"
   *   }
   * }
   * @response 401 { "ok": false, "error": "Token no proporcionado", "message": "Se requiere un token de autorización" }
   * @response 401 { "ok": false, "error": "Token expirado", "message": "El token ha expirado y no puede ser renovado" }
   * @response 401 { "ok": false, "error": "Token inválido", "message": "El token proporcionado no es válido" }
   * @response 500 { "ok": false, "error": "Error de token", "message": "Error al procesar el token" }
   */
  public function renew(RenewTokenRequest $request)
  {
    try {
      $token = $request->input('token');

      JWTAuth::setToken($token);
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
   * Invalida el **Bearer Token** actual.
   *
   * @group Auth
   * @authenticated
   * @header Authorization string required Token JWT actual en formato `Bearer {token}`.
   *
   * @response { "message": "Usuario desconectado correctamente" }
   * @response 401 { "ok": false, "message": "No autorizado" }
   */
  public function logout(LogoutRequest $request)
  {
    $token = $request->input('token');

    JWTAuth::setToken($token);
    JWTAuth::invalidate();
    return response()->json(['message' => 'Usuario desconectado correctamente']);
  }
}
