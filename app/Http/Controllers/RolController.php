<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rol;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ApiResponse;
use Illuminate\Validation\ValidationException;
use App\Exceptions\ServerErrorException;

class RolController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api');
    }
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            // Dejar que las excepciones de API lleguen al Handler
            throw $e;
        } catch (\Exception $e) {
            throw new ServerErrorException('Error al obtener los roles', null, $e);
        }
    }

    public function create(Request $request)
    {
        return $this->errorResponse('Función no implementada aún.', 501);
    }

    public function update(Request $request, $id)
    {
        return $this->errorResponse('Función no implementada aún.', 501);
    }

    public function delete($id)
    {
        return $this->errorResponse('Función no implementada aún.', 501);
    }

}
