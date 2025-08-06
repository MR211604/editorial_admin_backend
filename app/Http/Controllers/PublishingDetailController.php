<?php


namespace App\Http\Controllers;
use App\Http\Traits\ApiResponse;

use App\PublishingDetail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;


class PublishingDetailController extends Controller
{

  use ApiResponse;

  public function __construct()
  {
    $this->middleware('auth:api');
  }

  public function create(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'title' => 'required|string',
        'summary' => 'required|string',
        'URL' => 'nullable|string',
        'repository_URL' => 'nullable|string',
        'publishing_id' => 'required|integer|exists:publishings,id',
      ]);

      if ($validator->fails()) {
        return $this->errorResponse('Parámetros inválidos', 422, $validator->errors());
      }

      $data = $request->only(['title', 'summary', 'URL', 'repository_URL', 'publishing_id']);

      $publishingDetail = PublishingDetail::create($data);

      return $this->successResponse($publishingDetail, 201);
    } catch (\Exception $e) {
      return $this->errorResponse('Error al crear el detalle de la publicación', 500, $e->getMessage());
    }
  }

  public function update(Request $request, $id = null)
  {
    try {
      $validator = Validator::make($request->all(), [
        'title' => 'required|string',
        'summary' => 'required|string',
        'URL' => 'nullable|string',
        'repository_URL' => 'nullable|string',
        'publishing_id' => 'required|integer|exists:publishings,id',
      ]);

      if ($validator->fails()) {
        return $this->errorResponse('Parámetros inválidos', 422, $validator->errors());
      }

      $publishingDetail = PublishingDetail::find($id);

      if (!$publishingDetail) {
        return $this->errorResponse('Detalle de publicación no encontrado', 404);
      }

      $publishingDetail->update($request->only(['title', 'summary', 'URL', 'repository_URL', 'publishing_id']));

      return $this->successResponse($publishingDetail);
    } catch (\Exception $e) {
      return $this->errorResponse('Error al actualizar el detalle de la publicación', 500, $e->getMessage());
    }
  }

}