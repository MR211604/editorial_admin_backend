<?php


namespace App\Http\Controllers;
use App\Exceptions\BadRequestException;
use App\Http\Traits\ApiResponse;
use App\PublishingDetail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerErrorException;

/**
 * @resource PublishingDetail
 *
 * Endpoints para la gestión de detalles de publicaciones (revistas).
 * Usa JWT en el header: `Authorization: Bearer {token}`.
 */
class PublishingDetailController extends Controller
{

  use ApiResponse;

  /**
   * Constructor (middleware)
   *
   * Aplica el middleware `auth:api` a todas las rutas.
   */
  public function __construct()
  {
    $this->middleware('auth:api');
  }

  // Para crear un articulo de publicación, se necesita el language_shortname para diferenciar el lenguaja. Debido a que la informacion no se comparte, se necesita crear un articulo por cada lenguaje.
  public function create(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'title' => 'required|string',
        'summary' => 'required|string',
        'URL' => 'nullable|string',
        'repository_URL' => 'nullable|string',
        'language_id' => 'required|integer|exists:languages,id',
        'publishing_id' => 'required|integer|exists:publishings,id',
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $data = $request->only(['title', 'summary', 'URL', 'repository_URL', 'publishing_id', 'language_id']);

      $publishingDetail = PublishingDetail::create($data);

      return $this->successResponse($publishingDetail, 'Detalle de publicación creado', null, 201);
    } catch (ValidationException $e) {
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al crear el detalle de la publicación', null, $e);
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
        throw new ValidationException($validator);
      }

      $publishingDetail = PublishingDetail::find($id);

      if (!$publishingDetail) {
        throw new NotFoundException('Detalle de publicación no encontrado');
      }

      $publishingDetail->update($request->only(['title', 'summary', 'URL', 'repository_URL', 'publishing_id']));

      return $this->successResponse($publishingDetail);
    } catch (ValidationException $e) {
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al actualizar articulo de publicación', null, $e);
    }
  }

  public function delete($id = null)
  {
    try {
      if (empty($id) || !is_numeric($id)) {
        throw new BadRequestException('El ID de publicación es requerido');
      }

      $publishingDetail = PublishingDetail::find($id);

      if (!$publishingDetail) {
        throw new NotFoundException('Detalle de publicación no encontrado');
      }

      $publishingDetail->delete();

      return $this->successResponse(null, 'Detalle de publicación eliminado exitosamente');

    } catch (\Exception $e) {
      throw new ServerErrorException('Error al eliminar articulo de publicación', null, $e);
    }
  }

}
