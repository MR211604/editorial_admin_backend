<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rol;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ApiResponse;

class RolController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('api:auth');
    }
    public function index(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'limit' => 'integer|min:10'
            ]);

            if ($validator->fails()) {
                return $this->validationResponse($validator->errors(), 'Parámetros inválidos');
            }

            $page = $request->query('page', 1);
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

            return $this->successResponse($data, 'Roles obtenidos exitosamente');

        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los roles: ' . $e->getMessage(), 500);
        }
    }

}