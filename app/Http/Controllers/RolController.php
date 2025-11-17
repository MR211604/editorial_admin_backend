<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rol;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ApiResponse;
use Illuminate\Validation\ValidationException;
use App\Exceptions\ServerErrorException;

/**
 * @resource Roles
 *
 * Endpoints para la gestión de roles de usuarios.
 * Usa JWT en el header: `Authorization: Bearer {token}`.
 */
class RolController extends Controller
{
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
     * Listar roles
     * 
     * Obtiene todos los roles disponibles en el sistema.
     * 
     * **Parámetros de consulta (Query Parameters):**
     * - `page` (integer, opcional): Número de página (mínimo 1). Ejemplo: `1`
     * - `limit` (integer, opcional): Resultados por página (mínimo 10). Ejemplo: `10`
     * 
     * ```bash
     * curl -X GET "http://localhost/api/rol/getRol" \
     *   -H "Accept: application/json"
     * ```
     * 
     * Si se requiere paginación en el futuro, revisar el código comentado en el controlador.
     * Este endpoint NO requiere autenticación actualmente.
     * 
     * @response {
     *   "ok": true,
     *   "message": "Roles obtenidos exitosamente",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Administrador",
     *       "description": "Control total del sistema",
     *       "created_at": "2025-11-15 10:30:00",
     *       "updated_at": "2025-11-15 10:30:00"
     *     },
     *     {
     *       "id": 2,
     *       "name": "Editor",
     *       "description": "Puede editar publicaciones",
     *       "created_at": "2025-11-15 10:30:00",
     *       "updated_at": "2025-11-15 10:30:00"
     *     },
     *     {
     *       "id": 3,
     *       "name": "Revisor",
     *       "description": "Puede revisar contenido",
     *       "created_at": "2025-11-15 10:30:00",
     *       "updated_at": "2025-11-15 10:30:00"
     *     }
     *   ]
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
     */
    public function index(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'limit' => 'integer|min:10'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = Rol::get();
            return $this->successResponse($data, 'Roles obtenidos exitosamente');

            //! Si quieren paginarse los datos, descomentar el siguiente bloque:
            // $limit = $request->query('limit', 10);
            // $roles = Rol::paginate($limit);
            // $data = [
            //     'roles' => $roles->items(),
            //     'pagination' => [
            //         'current_page' => $roles->currentPage(),
            //         'last_page' => $roles->lastPage(),
            //         'per_page' => $roles->perPage(),
            //         'total' => $roles->total(),
            //         'from' => $roles->firstItem(),
            //         'to' => $roles->lastItem(),
            //     ]
            // ];

            // return $this->successResponse($data['roles'], 'Roles obtenidos exitosamente', $data['pagination']);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            // Dejar que las excepciones de API lleguen al Handler
            throw $e;
        } catch (\Exception $e) {
            throw new ServerErrorException('Error al obtener los roles', null, $e);
        }
    }

    /**
     * Crear un rol
     * 
     * Crea un nuevo rol en el sistema.
     * 
     * **Parámetros del Body:**
     * - `name` (string, requerido): Nombre del rol (máximo 255 caracteres, debe ser único). Ejemplo: `Moderador`
     * - `description` (string, opcional): Descripción del rol (máximo 500 caracteres). Ejemplo: `Puede moderar contenido`
     * 
     * ```json
     * {
     *   "name": "Moderador",
     *   "description": "Puede moderar contenido y aprobar publicaciones"
     * }
     * ```
     * 
     * ```bash
     * curl -X POST "http://localhost/api/rol/createRol" \
     *   -H "Accept: application/json" \
     *   -H "Content-Type: application/json" \
     *   -d '{"name":"Moderador","description":"Puede moderar contenido"}'
     * ```
     * 
     * @response 201 {
     *   "ok": true,
     *   "message": "Rol creado exitosamente",
     *   "data": {
     *     "id": 4,
     *     "name": "Moderador",
     *     "description": "Puede moderar contenido y aprobar publicaciones",
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
     *     "name": ["The name field is required.", "The name has already been taken."],
     *     "description": ["The description may not be greater than 500 characters."]
     *   }
     * }
     */
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:rol,name',
                'description' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $rol = Rol::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            return $this->successResponse($rol, 'Rol creado exitosamente', null, 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ServerErrorException('Error al crear el rol', null, $e);
        }
    }

}
