<?php


namespace App\Http\Controllers;
use App\Exceptions\BadRequestException;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\NormalizesMdxImages;
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
  use NormalizesMdxImages;

  /**
   * Constructor (middleware)
   *
   * Aplica el middleware `auth:api` a todas las rutas.
   */
  public function __construct()
  {
    $this->middleware('auth:api');
  }

  /**
   * Crear detalle de publicación
   * 
   * Crea un artículo o detalle de una publicación (revista) en un idioma específico.
   * Cada publicación puede tener múltiples detalles, uno por cada idioma disponible.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros del Body:**
   * - `title` (string, requerido): Título del artículo/detalle. Ejemplo: `Avances en Inteligencia Artificial`
   * - `summary` (string, opcional): Resumen del artículo. Ejemplo: `Este artículo explora los últimos avances...`
   * - `URL` (string, opcional): URL del artículo completo. Ejemplo: `https://revista.udb.edu.sv/articulo/123`
   * - `repository_URL` (string, opcional): URL del repositorio académico. Ejemplo: `https://repositorio.udb.edu.sv/123`
   * - `language_id` (integer, requerido): ID del idioma (debe existir en tabla languages). Ejemplo: `1`
   * - `publishing_id` (integer, requerido): ID de la publicación padre (debe existir en tabla publishings). Ejemplo: `5`
   * 
   * ```json
   * {
   *   "title": "Avances en Inteligencia Artificial",
   *   "summary": "Este artículo explora los últimos avances en IA aplicados a la educación superior",
   *   "URL": "https://revista.udb.edu.sv/articulo/123",
   *   "repository_URL": "https://repositorio.udb.edu.sv/123",
   *   "language_id": 1,
   *   "publishing_id": 5
   * }
   * ```
   * 
   * ```bash
   * curl -X POST "http://localhost/api/publishingDetails/createPublishingDetail" \
   *   -H "Accept: application/json" \
   *   -H "Content-Type: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
   *   -d '{
   *     "title":"Avances en IA",
   *     "summary":"Resumen del artículo",
   *     "URL":"https://revista.udb.edu.sv/articulo/123",
   *     "language_id":1,
   *     "publishing_id":5
   *   }'
   * ```
   * 
   * **Nota importante:** 
   * - La información NO se comparte entre idiomas, se debe crear un detalle por cada idioma
   * - Los campos `URL` y `repository_URL` son opcionales
   * - El `publishing_id` debe corresponder a una publicación de tipo revista existente
   * 
   * @authenticated
   * 
   * @response 201 {
   *   "ok": true,
   *   "message": "Detalle de publicación creado",
   *   "data": {
   *     "id": 1,
   *     "title": "Avances en Inteligencia Artificial",
   *     "summary": "Este artículo explora los últimos avances en IA aplicados a la educación superior",
   *     "URL": "https://revista.udb.edu.sv/articulo/123",
   *     "repository_URL": "https://repositorio.udb.edu.sv/123",
   *     "language_id": 1,
   *     "publishing_id": 5,
   *     "created_at": "2025-11-17 10:00:00",
   *     "updated_at": "2025-11-17 10:00:00"
   *   }
   * }
   * 
   * @response 422 {
   *   "ok": false,
   *   "error": "Validation Error",
   *   "message": "Los datos proporcionados no son válidos",
   *   "errors": {
   *     "title": ["The title field is required."],
   *     "language_id": ["The language id field is required.", "The selected language id is invalid."],
   *     "publishing_id": ["The publishing id field is required.", "The selected publishing id is invalid."]
   *   }
   * }
   * 
   * @response 401 {
   *   "message": "Unauthenticated"
   * }
   */
  public function create(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'title' => 'required|string',
        'summary' => 'nullable|string',
        'URL' => 'nullable|string',
        'repository_URL' => 'nullable|string',
        'language_id' => 'required|integer|exists:languages,idLanguage',
        'publishing_id' => 'required|integer|exists:publishings,idPublishing',
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $data = $request->only(['title', 'summary', 'URL', 'repository_URL', 'publishing_id', 'language_id']);
      if (array_key_exists('summary', $data)) {
        $data['summary'] = $this->normalizeMdxImageUrls($data['summary']);
      }

      $publishingDetail = PublishingDetail::create($data);

      return $this->successResponse($publishingDetail, 'Detalle de publicación creado', null, 201);
    } catch (ValidationException $e) {
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al crear el detalle de la publicación', null, $e);
    }
  }

  /**
   * Actualizar detalle de publicación
   * 
   * Actualiza la información de un artículo o detalle de publicación existente.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros de ruta (URL Parameters):**
   * - `id` (integer, requerido): ID del detalle de publicación a actualizar. Ejemplo: `1`
   * 
   * **Parámetros del Body:**
   * - `title` (string, requerido): Nuevo título del artículo. Ejemplo: `Inteligencia Artificial en la Educación`
   * - `summary` (string, opcional): Nuevo resumen del artículo. Ejemplo: `Resumen actualizado del contenido...`
   * - `URL` (string, opcional): Nueva URL del artículo completo. Ejemplo: `https://revista.udb.edu.sv/articulo/123-v2`
   * - `repository_URL` (string, opcional): Nueva URL del repositorio. Ejemplo: `https://repositorio.udb.edu.sv/123-v2`
   * - `publishing_id` (integer, requerido): ID de la publicación padre (debe existir en tabla publishings). Ejemplo: `5`
   * 
   * ```json
   * {
   *   "title": "Inteligencia Artificial en la Educación Superior",
   *   "summary": "Resumen actualizado con información adicional sobre IA en educación",
   *   "URL": "https://revista.udb.edu.sv/articulo/123-v2",
   *   "repository_URL": "https://repositorio.udb.edu.sv/123-v2",
   *   "publishing_id": 5
   * }
   * ```
   * 
   * ```bash
   * curl -X POST "http://localhost/api/publishingDetails/updatePublishingDetail/1" \
   *   -H "Accept: application/json" \
   *   -H "Content-Type: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
   *   -d '{
   *     "title":"IA en Educación Superior",
   *     "summary":"Resumen actualizado",
   *     "URL":"https://revista.udb.edu.sv/articulo/123-v2",
   *     "publishing_id":5
   *   }'
   * ```
   * 
   * **Nota:** El idioma (`language_id`) no se puede cambiar. Para cambiar el idioma, crea un nuevo detalle.
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Success",
   *   "data": {
   *     "id": 1,
   *     "title": "Inteligencia Artificial en la Educación Superior",
   *     "summary": "Resumen actualizado con información adicional sobre IA en educación",
   *     "URL": "https://revista.udb.edu.sv/articulo/123-v2",
   *     "repository_URL": "https://repositorio.udb.edu.sv/123-v2",
   *     "language_id": 1,
   *     "publishing_id": 5,
   *     "created_at": "2025-11-17 10:00:00",
   *     "updated_at": "2025-11-17 12:30:00"
   *   }
   * }
   * 
   * @response 404 {
   *   "ok": false,
   *   "error": "Not Found",
   *   "message": "Detalle de publicación no encontrado"
   * }
   * 
   * @response 422 {
   *   "ok": false,
   *   "error": "Validation Error",
   *   "message": "Los datos proporcionados no son válidos",
   *   "errors": {
   *     "title": ["The title field is required."],
   *     "publishing_id": ["The publishing id field is required.", "The selected publishing id is invalid."]
   *   }
   * }
   * 
   * @response 401 {
   *   "message": "Unauthenticated"
   * }
   */
  public function update(Request $request, $id = null)
  {
    try {
      $validator = Validator::make($request->all(), [
        'title' => 'required|string',
        'summary' => 'nullable|string',
        'URL' => 'nullable|string',
        'repository_URL' => 'nullable|string',
        'publishing_id' => 'required|integer|exists:publishings,idPublishing',
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $publishingDetail = PublishingDetail::find($id);

      if (!$publishingDetail) {
        throw new NotFoundException('Detalle de publicación no encontrado');
      }

      $detailData = $request->only(['title', 'summary', 'URL', 'repository_URL', 'publishing_id']);
      if (array_key_exists('summary', $detailData)) {
        $detailData['summary'] = $this->normalizeMdxImageUrls($detailData['summary']);
      }
      $publishingDetail->update($detailData);

      return $this->successResponse($publishingDetail);
    } catch (ValidationException $e) {
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al actualizar articulo de publicación', null, $e);
    }
  }

  /**
   * Eliminar detalle de publicación
   * 
   * Elimina un artículo o detalle de publicación del sistema.
   * Esta acción es permanente y no se puede deshacer.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros de ruta (URL Parameters):**
   * - `id` (integer, requerido): ID del detalle de publicación a eliminar. Ejemplo: `1`
   * 
   * ```bash
   * curl -X POST "http://localhost/api/publishingDetails/deletePublishingDetail/1" \
   *   -H "Accept: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
   * ```
   * 
   * **Nota importante:** 
   * - Esta operación es irreversible
   * - Solo elimina el detalle en un idioma específico, no la publicación completa
   * - Para eliminar todos los detalles de una publicación, debe eliminar cada uno individualmente
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Detalle de publicación eliminado exitosamente",
   *   "data": null
   * }
   * 
   * @response 400 {
   *   "ok": false,
   *   "error": "Bad Request",
   *   "message": "El ID de publicación es requerido"
   * }
   * 
   * @response 404 {
   *   "ok": false,
   *   "error": "Not Found",
   *   "message": "Detalle de publicación no encontrado"
   * }
   * 
   * @response 401 {
   *   "message": "Unauthenticated"
   * }
   */
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
