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

class AuthController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth:api', ['except' => ['login']]);
  }

  public function login(Request $request)
  {
    $credentials = $request->only("token", "password");

    try {

      if (!$token = JWTAuth::attempt($credentials)) {
        return response()->json(['error' => 'Credenciales incorrectas'], 401);
      }

      $user = Auth::user();


      if($user->active === 0) {
        return response()->json(['error' => 'Usuario desactivado'], 400);
      }

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

  public function logout()
  {
    JWTAuth::invalidate(JWTAuth::getToken());
    return response()->json(['message' => 'Usuario desconectado correctamente']);
  }
}
