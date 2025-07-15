<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\UserPrivilege;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
}