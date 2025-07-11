<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Http\Traits\SearchFilter;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    use SearchFilter;
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
                'limit' => 'integer|min:10',
                'rol_id' => 'integer'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    'Parámetros inválidos',
                    422,
                    $validator->errors()
                );
            }

            $limit = $request->query('limit', 10);
            $rol_id = $request->query('rol_id', null);
            $search = $request->query('search', null);

            $query = User::with(['rol'])
                ->when($rol_id, function ($query) use ($rol_id) {
                    return $query->where('rol_id', $rol_id);
                });
            $allowedSortFields = ['id', 'name', 'rol_id'];

            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }

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

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener los usuarios',
                500
            );
        }
    }

    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:users,name',
                'password' => 'required|string|min:8',
                'rol_id' => 'required|integer|exists:rol,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->input('name'),
                'password' => bcrypt($request->input('password')),
                'rol_id' => $request->input('rol_id'),
                'active' => 1,
                'join_date' => null,
                'last_login_date' => null,
                'token' => $request->input('name')
            ]);

            return response()->json([
                'ok' => true,
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Error al crear el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
