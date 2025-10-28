<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rol\RolIndexRequest;
use App\Rol;
use App\Http\Traits\ApiResponse;
use App\Exceptions\ServerErrorException;
use Illuminate\Http\Request;

/**
 * @resource Roles
 *
 * Endpoints para gestionar roles de usuario.
 */
class RolController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Listar roles
     *
     * Devuelve un listado paginado de roles.
     *
     * @group Roles
     * @queryParam page integer Número de página. Example: 1
     * @queryParam limit integer Cantidad de elementos por página (mínimo 10). Example: 15
     *
     * @response {
     *   "ok": true,
     *   "message": "Roles obtenidos exitosamente",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Administrador"
     *     }
     *   ],
     *   "pagination": {
     *     "current_page": 1,
     *     "last_page": 2,
     *     "per_page": 10,
     *     "total": 12,
     *     "from": 1,
     *     "to": 10
     *   }
     * }
     */
    public function index(RolIndexRequest $request)
    {
        try {
            $limit = $request->query('limit', 10);

            $roles = Rol::paginate($limit);

            $data = [
                'roles' => $roles->items(),
                'pagination' => [
                    'current_page' => $roles->currentPage(),
                    'last_page' => $roles->lastPage(),
                    'per_page' => $roles->perPage(),
                    'total' => $roles->total(),
                    'from' => $roles->firstItem(),
                    'to' => $roles->lastItem(),
                ]
            ];

            return $this->successResponse($data['roles'], 'Roles obtenidos exitosamente', $data['pagination']);

        } catch (\App\Exceptions\ApiException $e) {
            // Dejar que las excepciones de API lleguen al Handler
            throw $e;
        } catch (\Exception $e) {
            throw new ServerErrorException('Error al obtener los roles', null, $e);
        }
    }

    /**
     * Crear rol
     *
     * Endpoint reservado para la creación de roles.
     *
     * @group Roles
     *
     * @response 501 {
     *   "ok": false,
     *   "message": "Función no implementada aún."
     * }
     */
    public function create(Request $request)
    {
        return $this->errorResponse('Función no implementada aún.', 501);
    }

    /**
     * Actualizar rol
     *
     * Endpoint reservado para actualizar roles existentes.
     *
     * @group Roles
     * @urlParam id integer required Identificador del rol. Example: 3
     *
     * @response 501 {
     *   "ok": false,
     *   "message": "Función no implementada aún."
     * }
     */
    public function update(Request $request, $id)
    {
        return $this->errorResponse('Función no implementada aún.', 501);
    }

    /**
     * Eliminar rol
     *
     * Endpoint reservado para eliminar roles.
     *
     * @group Roles
     * @urlParam id integer required Identificador del rol. Example: 5
     *
     * @response 501 {
     *   "ok": false,
     *   "message": "Función no implementada aún."
     * }
     */
    public function delete($id)
    {
        return $this->errorResponse('Función no implementada aún.', 501);
    }

}
