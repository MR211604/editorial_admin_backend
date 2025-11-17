<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\SearchFilter;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Exceptions\BadRequestException;
use App\Exceptions\ServerErrorException;

/**
 * @resource Users
 *
 * Endpoints para la gestión de usuarios del sistema.
 * Usa JWT en el header: `Authorization: Bearer {token}`.
 */
class UserController extends Controller
{

    use SearchFilter;
    use ApiResponse;

    /**
     * Constructor (middleware)
     *
     * Aplica el middleware `auth:api` a todas las rutas.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Listar usuarios
     * 
     * Obtiene una lista paginada de usuarios con sus roles asociados. 
     * Permite filtrar por rol y buscar por nombre.
     * 
     * **Autenticación requerida:**
     * Este endpoint requiere un token JWT válido en el header de autorización.
     * 
     * **Parámetros de consulta (Query Parameters):**
     * - `page` (integer, opcional): Número de página (mínimo 1). Por defecto: 1. Ejemplo: `1`
     * - `limit` (integer, opcional): Resultados por página (mínimo 10). Por defecto: 10. Ejemplo: `10`
     * - `rol_id` (integer, opcional): Filtrar usuarios por rol. Ejemplo: `1`
     * - `search` (string, opcional): Buscar usuarios por nombre. Ejemplo: `Juan`
     * - `sort` (string, opcional): Ordenar por campo (id, name, token). Ejemplo: `name:asc`
     * 
     * ```bash
     * # Listar todos los usuarios
     * curl -X GET "http://localhost/api/users/getUsers?page=1&limit=10" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
     * 
     * # Filtrar por rol y buscar por nombre
     * curl -X GET "http://localhost/api/users/getUsers?rol_id=1&search=Juan" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
     * ```
     * 
     * @authenticated
     * 
     * @response {
     *   "ok": true,
     *   "message": "Usuarios obtenidos exitosamente",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Juan Pérez",
     *       "token": "juanperez",
     *       "rol_id": 1,
     *       "active": 1,
     *       "join_date": "2025-01-15 10:00:00",
     *       "last_login_date": "2025-11-15 09:30:00",
     *       "created_at": "2025-01-15 10:00:00",
     *       "updated_at": "2025-11-15 09:30:00",
     *       "rol": {
     *         "id": 1,
     *         "name": "Administrador",
     *         "description": "Control total del sistema"
     *       }
     *     },
     *     {
     *       "id": 2,
     *       "name": "María López",
     *       "token": "marialopez",
     *       "rol_id": 2,
     *       "active": 1,
     *       "join_date": "2025-02-20 11:00:00",
     *       "last_login_date": "2025-11-14 16:45:00",
     *       "created_at": "2025-02-20 11:00:00",
     *       "updated_at": "2025-11-14 16:45:00",
     *       "rol": {
     *         "id": 2,
     *         "name": "Editor",
     *         "description": "Puede editar publicaciones"
     *       }
     *     }
     *   ],
     *   "pagination": {
     *     "total": 50,
     *     "current_page": 1,
     *     "last_page": 5,
     *     "per_page": 10,
     *     "from": 1,
     *     "to": 10
     *   }
     * }
     * 
     * @response 422 {
     *   "ok": false,
     *   "error": "Validation Error",
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "page": ["The page must be at least 1."],
     *     "limit": ["The limit must be at least 10."]
     *   }
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'limit' => 'integer|min:10',
                'rol_id' => 'integer'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $limit = $request->query('limit', 10);
            $rol_id = $request->query('rol_id', null);
            $search = $request->query('search', null);

            $query = User::with(['rol'])
                ->when($rol_id, function ($query) use ($rol_id) {
                    return $query->where('rol_id', $rol_id);
                });

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            }

            $allowedSortFields = ['id', 'name', 'token'];

            if ($request->has('sort')) {
                $this->search($request, $query, $allowedSortFields);
            } else {
                $query->orderBy('id', 'desc');
            }

            $users = $query->paginate($limit);

            $pagination = [
                'total' => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem()
            ];

            return $this->successResponse(
                $users->items(),
                'Usuarios obtenidos exitosamente',
                $pagination
            );

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ServerErrorException('Error al obtener los usuarios', null, $e);
        }
    }

    /**
     * Crear un usuario
     * 
     * Crea un nuevo usuario en el sistema con su rol asignado.
     * El usuario se crea activo por defecto y se establece una relación con el rol.
     * 
     * **Autenticación requerida:**
     * Este endpoint requiere un token JWT válido en el header de autorización.
     * 
     * **Parámetros del Body:**
     * - `name` (string, requerido): Nombre del usuario (mínimo 2, máximo 255 caracteres, debe ser único). Ejemplo: `Carlos Ramírez`
     * - `password` (string, requerido): Contraseña del usuario (mínimo 8 caracteres). Ejemplo: `SecurePass123`
     * - `rol_id` (integer, requerido): ID del rol a asignar (debe existir en la tabla roles). Ejemplo: `2`
     * 
     * ```json
     * {
     *   "name": "Carlos Ramírez",
     *   "password": "SecurePass123",
     *   "rol_id": 2
     * }
     * ```
     * 
     * ```bash
     * curl -X POST "http://localhost/api/users/createUser" \
     *   -H "Accept: application/json" \
     *   -H "Content-Type: application/json" \
     *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
     *   -d '{"name":"Carlos Ramírez","password":"SecurePass123","rol_id":2}'
     * ```
     * 
     * **Nota:** 
     * - La contraseña se encripta automáticamente con bcrypt
     * - El campo `token` se genera automáticamente igual al `name`
     * - El usuario se crea activo (`active=1`) por defecto
     * - Se crea una relación en la tabla pivot `user_rol`
     * - La operación usa transacción de base de datos
     * 
     * @authenticated
     * 
     * @response 201 {
     *   "ok": true,
     *   "message": "Usuario creado exitosamente",
     *   "data": {
     *     "id": 3,
     *     "name": "Carlos Ramírez",
     *     "token": "Carlos Ramírez",
     *     "rol_id": 2,
     *     "active": 1,
     *     "join_date": null,
     *     "last_login_date": null,
     *     "created_at": "2025-11-15 12:00:00",
     *     "updated_at": "2025-11-15 12:00:00"
     *   }
     * }
     * 
     * @response 422 {
     *   "ok": false,
     *   "error": "Validation Error",
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "name": ["The name field is required.", "The name has already been taken.", "The name must be at least 2 characters."],
     *     "password": ["The password field is required.", "The password must be at least 8 characters."],
     *     "rol_id": ["The rol id field is required.", "The selected rol id is invalid."]
     *   }
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:2|max:255|unique:users,name',
                'password' => 'required|string|min:8',
                'rol_id' => 'required|integer|exists:rol,id'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $user = DB::transaction(function () use ($request, &$user) {
                $user = User::create([
                    'name' => $request->input('name'),
                    'password' => bcrypt($request->input('password')),
                    'rol_id' => $request->input('rol_id'),
                    'active' => 1,
                    'join_date' => null,
                    'last_login_date' => null,
                    'token' => $request->input('name')
                ]);

                $user->userRoles()->attach($request->input('rol_id'));

                return $user;
            });

            return $this->successResponse(
                $user,
                'Usuario creado exitosamente',
                null,
                201
            );

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ServerErrorException('Error al crear el usuario', null, $e);
        }
    }

    /**
     * Activar/Desactivar usuario
     * 
     * Alterna el estado de un usuario entre activo e inactivo.
     * Los usuarios inactivos no podrán iniciar sesión en el sistema.
     * 
     * **Autenticación requerida:**
     * Este endpoint requiere un token JWT válido en el header de autorización.
     * 
     * **Parámetros de ruta (URL Parameters):**
     * - `id` (integer, requerido): ID del usuario cuyo estado se alternará. Ejemplo: `1`
     * 
     * ```bash
     * curl -X POST "http://localhost/api/users/toggleActive/1" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
     * ```
     * 
     * **Nota:** Esta operación es reversible. Puede activar o desactivar usuarios según sea necesario.
     * 
     * @authenticated
     * 
     * @response {
     *   "ok": true,
     *   "message": "Usuario activado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "name": "Juan Pérez",
     *     "token": "juanperez",
     *     "rol_id": 1,
     *     "active": 1,
     *     "join_date": "2025-01-15 10:00:00",
     *     "last_login_date": "2025-11-15 09:30:00",
     *     "created_at": "2025-01-15 10:00:00",
     *     "updated_at": "2025-11-15 12:30:00"
     *   }
     * }
     * 
     * @response {
     *   "ok": true,
     *   "message": "Usuario desactivado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "name": "Juan Pérez",
     *     "token": "juanperez",
     *     "rol_id": 1,
     *     "active": 0,
     *     "join_date": "2025-01-15 10:00:00",
     *     "last_login_date": "2025-11-15 09:30:00",
     *     "created_at": "2025-01-15 10:00:00",
     *     "updated_at": "2025-11-15 12:30:00"
     *   }
     * }
     * 
     * @response 400 {
     *   "ok": false,
     *   "error": "Bad Request",
     *   "message": "El ID del usuario es requerido"
     * }
     * 
     * @response 404 {
     *   "ok": false,
     *   "error": "Not Found",
     *   "message": "Usuario no encontrado"
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    public function toggleActive($id = null)
    {
        try {

            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El ID del usuario es requerido');
            }

            $user = User::findOrFail($id);
            $user->active = !$user->active;
            $user->save();

            $message = $user->active ? 'Usuario activado exitosamente' : 'Usuario desactivado exitosamente';

            return $this->successResponse(
                $user,
                $message
            );

        } catch (\Exception $e) {
            throw new ServerErrorException('Error al cambiar el estado del usuario', null, $e);
        }
    }

    /**
     * Actualizar un usuario
     * 
     * Actualiza la información de un usuario existente, incluyendo su nombre y rol asignado.
     * 
     * **Autenticación requerida:**
     * Este endpoint requiere un token JWT válido en el header de autorización.
     * 
     * **Parámetros de ruta (URL Parameters):**
     * - `id` (integer, requerido): ID del usuario a actualizar. Ejemplo: `1`
     * 
     * **Parámetros del Body:**
     * - `name` (string, requerido): Nuevo nombre del usuario (mínimo 2, máximo 255 caracteres, debe ser único excepto para el mismo usuario). Ejemplo: `Juan Carlos Pérez`
     * - `rol_id` (integer, requerido): Nuevo ID del rol a asignar (debe existir en la tabla roles). Ejemplo: `1`
     * 
     * ```json
     * {
     *   "name": "Juan Carlos Pérez",
     *   "rol_id": 1
     * }
     * ```
     * 
     * ```bash
     * curl -X POST "http://localhost/api/users/updateUser/1" \
     *   -H "Accept: application/json" \
     *   -H "Content-Type: application/json" \
     *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
     *   -d '{"name":"Juan Carlos Pérez","rol_id":1}'
     * ```
     * 
     * **Nota:** 
     * - La relación de roles se sincroniza automáticamente en la tabla pivot `user_rol`
     * - La operación usa transacción de base de datos para garantizar consistencia
     * - No se puede actualizar la contraseña a través de este endpoint
     * 
     * @authenticated
     * 
     * @response {
     *   "ok": true,
     *   "message": "Usuario actualizado exitosamente",
     *   "data": {
     *     "id": 1,
     *     "name": "Juan Carlos Pérez",
     *     "token": "juanperez",
     *     "rol_id": 1,
     *     "active": 1,
     *     "join_date": "2025-01-15 10:00:00",
     *     "last_login_date": "2025-11-15 09:30:00",
     *     "created_at": "2025-01-15 10:00:00",
     *     "updated_at": "2025-11-15 13:00:00"
     *   }
     * }
     * 
     * @response 400 {
     *   "ok": false,
     *   "error": "Bad Request",
     *   "message": "El ID del usuario es obligatorio para la actualización"
     * }
     * 
     * @response 404 {
     *   "ok": false,
     *   "error": "Not Found",
     *   "message": "Usuario no encontrado"
     * }
     * 
     * @response 422 {
     *   "ok": false,
     *   "error": "Validation Error",
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "name": ["The name field is required.", "The name has already been taken."],
     *     "rol_id": ["The rol id field is required.", "The selected rol id is invalid."]
     *   }
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    public function update(Request $request, $id = null)
    {
        try {

            if (!$id) {
                throw new \InvalidArgumentException('El ID del usuario es obligatorio para la actualización');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:2|max:255|unique:users,name,' . $id,
                'rol_id' => 'required|integer|exists:rol,id'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $user = DB::transaction(function () use ($request, $id) {
                $user = User::findOrFail($id);
                $user->name = $request->input('name');
                $user->rol_id = $request->input('rol_id');
                $user->save();
                // Actualizar la relación de roles del usuario
                $user->userRoles()->sync([$request->input('rol_id')]);

                return $user;
            });

            return $this->successResponse(
                $user,
                'Usuario actualizado exitosamente'
            );

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ServerErrorException('Error al actualizar el usuario', null, $e);
        }
    }
}
