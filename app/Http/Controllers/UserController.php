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
