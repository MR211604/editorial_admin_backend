<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rol;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;

class RolController extends Controller
{

  public function index(Request $request) {
    try {

      $validator = Validator::make($request->all(), [
        'page' => 'integer|min:1',
        'limit' => 'integer|min:10'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Parámetros inválidos',
          'errors' => $validator->errors()
        ], 422);
      }

      $page = $request->query('page', 1);
      $limit = $request->query('limit', 10);

      $roles = Rol::paginate($limit);

      return response()->json([
        'ok' => true,
        'data' => $roles->items(),
        'pagination' => [
          'current_page' => $roles->currentPage(),
          'last_page' => $roles->lastPage(),
          'per_page' => $roles->perPage(),
          'total' => $roles->total(),
          'from' => $roles->firstItem(),
          'to' => $roles->lastItem(),
        ]
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'ok' => false,
        'message' => 'Error al obtener los roles',
        'error' => $e->getMessage()
      ], 500);
    }
  }

}