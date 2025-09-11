<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\UserPrivilege;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerErrorException;

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
        throw new ValidationException($validator);
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

    } catch (\Illuminate\Validation\ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las excepciones de API lleguen al Handler
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al obtener las autorizaciones', null, $e);
    }
  }

  public function find($id = null) {
    try {
      // Validar si el ID está presente y no está vacío
      if (empty($id) || !is_numeric($id)) {
        throw new BadRequestException('El numero de ID es requerido');
      }

      if(!UserPrivilege::where('id', $id)->exists()) {
        throw new NotFoundException('Privilegio no encontrado');
      }

      $privilege = UserPrivilege::findOrFail($id);
      return $this->successResponse($privilege, 'Privilegio obtenido exitosamente');
    } catch (\Illuminate\Validation\ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las excepciones de API lleguen al Handler
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al obtener el privilegio', null, $e);
    }
  }
}
