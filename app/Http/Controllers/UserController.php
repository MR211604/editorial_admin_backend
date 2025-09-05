<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Http\Traits\SearchFilter;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Exceptions\ServerErrorException;

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
                throw ValidationException::withMessages($validator->errors()->toArray());
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ServerErrorException('Error al obtener los usuarios', null, $e);
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
                throw ValidationException::withMessages($validator->errors()->toArray());
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ServerErrorException('Error al crear el usuario', null, $e);
        }
    }
}
