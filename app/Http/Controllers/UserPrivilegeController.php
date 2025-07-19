<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\UserPrivilege;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserPrivilegeController extends Controller
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
        return $this->validationResponse($validator->errors(), 'Parámetros inválidos');
      }
      $limit = $request->query('limit', 10);

      $privileges = UserPrivilege::paginate($limit);

      $pagination = [
        'current_page' => $privileges->currentPage(),
        'last_page' => $privileges->lastPage(),
        'per_page' => $privileges->perPage(),
        'total' => $privileges->total(),
        'from' => $privileges->firstItem(),
        'to' => $privileges->lastItem(),
      ];

      return $this->successResponse($privileges->items(), 'Autorizaciones obtenidas exitosamente', $pagination);

    } catch (\Exception $e) {
      return $this->errorResponse('Error retrieving privileges: ' . $e->getMessage(), 500);
    }
  }

  public function find($id = null) {
    try {
      // Validar si el ID está presente y no está vacío
      if (empty($id) || !is_numeric($id)) {
        return $this->errorResponse('El numero de ID es requerido', 400);
      }

      if(!UserPrivilege::where('id', $id)->exists()) {
        return $this->errorResponse('Privilegio no encontrado', 404);
      }

      $privilege = UserPrivilege::findOrFail($id);
      return $this->successResponse($privilege, 'Privilegio obtenido exitosamente');
    } catch (\Exception $e) {
      return $this->errorResponse('Error obteniendo privilegio: ' . $e->getMessage(), 500);
    }
  }
}