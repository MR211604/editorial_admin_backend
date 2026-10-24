<?php

namespace App\Http\Controllers;

use App\EditorialLocation;
use App\Exceptions\BadRequestException;
use App\Exceptions\ServerErrorException;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @resource Ubicaciones editoriales
 *
 * Endpoints para consultar y administrar las ubicaciones mostradas en el sitio editorial.
 * Los endpoints protegidos usan JWT en el header: `Authorization: Bearer {token}`.
 */
class EditorialLocationController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api')->except(['active']);
    }

    /**
     * Listar ubicaciones
     *
     * Obtiene todas las ubicaciones, activas e inactivas, ordenadas por `sort_order`
     * y luego por `idLocation`.
     *
     * **Autenticación requerida:** Sí.
     *
     * ```bash
     * curl -X GET "http://localhost/api/locations/getLocations" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer {token}"
     * ```
     *
     * @authenticated
     *
     * @response {
     *   "ok": true,
     *   "message": "Ubicaciones obtenidas correctamente",
     *   "data": [
     *     {
     *       "idLocation": 1,
     *       "name": "Universidad Don Bosco",
     *       "image": "img/logos_ubicaciones/udb.png",
     *       "address": "Campus Soyapango y Campus Antiguo Cuscatlán",
     *       "mapsQuery": "Universidad Don Bosco Campus Soyapango El Salvador",
     *       "state": 1,
     *       "sort_order": 1,
     *       "created_at": "2026-10-23 00:05:27",
     *       "updated_at": "2026-10-23 00:05:27"
     *     }
     *   ]
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    public function index()
    {
        try {
            $locations = EditorialLocation::orderBy('sort_order')
                ->orderBy('idLocation')
                ->get();

            return $this->successResponse($locations, 'Ubicaciones obtenidas correctamente');
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener las ubicaciones', null, $e);
        }
    }

    /**
     * Listar ubicaciones activas
     *
     * Obtiene únicamente las ubicaciones activas. La respuesta incluye `idLocation`,
     * `name`, `image`, `address`, `mapsQuery` y `sort_order`. Este endpoint es público.
     *
     * ```bash
     * curl -X GET "http://localhost/api/locations/getActiveLocations" \
     *   -H "Accept: application/json"
     * ```
     *
     * @response {
     *   "ok": true,
     *   "message": "Ubicaciones activas obtenidas correctamente",
     *   "data": [
     *     {
     *       "idLocation": 1,
     *       "name": "Universidad Don Bosco",
     *       "image": "img/logos_ubicaciones/udb.png",
     *       "address": "Campus Soyapango y Campus Antiguo Cuscatlán",
     *       "mapsQuery": "Universidad Don Bosco Campus Soyapango El Salvador",
     *       "sort_order": 1
     *     }
     *   ]
     * }
     */
    public function active()
    {
        try {
            $locations = EditorialLocation::where('state', 1)
                ->orderBy('sort_order')
                ->orderBy('idLocation')
                ->get(['idLocation', 'name', 'image', 'address', 'mapsQuery', 'sort_order']);

            return $this->successResponse($locations, 'Ubicaciones activas obtenidas correctamente');
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener las ubicaciones activas', null, $e);
        }
    }

    /**
     * Crear una ubicación
     *
     * Crea una ubicación nueva. El campo `image` puede ser el nombre del archivo
     * o una ruta relativa bajo `img/logos_ubicaciones/`; si se envía una URL que
     * contiene `imageslocations/`, se normaliza al nombre del archivo.
     *
     * **Parámetros del body (JSON):**
     * - `name` (string, requerido): Nombre, máximo 255 caracteres.
     * - `image` (string, requerido): Nombre/ruta de imagen permitida, máximo 2048 caracteres.
     * - `address` (string, requerido): Dirección, máximo 500 caracteres.
     * - `mapsQuery` (string, requerido): Texto de búsqueda para mapas, máximo 500 caracteres.
     * - `state` (boolean, requerido): Estado (0=inactiva, 1=activa).
     * - `sort_order` (integer, requerido): Orden de visualización, mínimo `0`.
     *
     * **Autenticación requerida:** Sí.
     *
     * ```bash
     * curl -X POST "http://localhost/api/locations/createLocation" \
     *   -H "Accept: application/json" \
     *   -H "Content-Type: application/json" \
     *   -H "Authorization: Bearer {token}" \
     *   -d '{"name":"Universidad Don Bosco","image":"img/logos_ubicaciones/udb.png","address":"Campus Soyapango","mapsQuery":"Universidad Don Bosco El Salvador","state":1,"sort_order":1}'
     * ```
     *
     * @authenticated
     *
     * @response 201 {
     *   "ok": true,
     *   "message": "Ubicación creada correctamente",
     *   "data": {
     *     "idLocation": 1,
     *     "name": "Universidad Don Bosco",
     *     "image": "img/logos_ubicaciones/udb.png",
     *     "address": "Campus Soyapango",
     *     "mapsQuery": "Universidad Don Bosco El Salvador",
     *     "state": 1,
     *     "sort_order": 1
     *   }
     * }
     *
     * @response 422 {
     *   "ok": false,
     *   "error": "Validation Error",
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "name": ["The name field is required."],
     *     "image": ["The image field is required."],
     *     "state": ["The state field is required."]
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
            $request->merge(['image' => $this->normalizeImagePath($request->input('image'))]);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'image' => ['required', 'string', 'max:2048', 'regex:/^(?:[A-Za-z0-9_-][A-Za-z0-9._-]*|img\/logos_ubicaciones\/[A-Za-z0-9_-][A-Za-z0-9._-]*)$/'],
                'address' => 'required|string|max:500',
                'mapsQuery' => 'required|string|max:500',
                'state' => 'required|boolean',
                'sort_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $location = EditorialLocation::create($request->only([
                'name', 'image', 'address', 'mapsQuery', 'state', 'sort_order',
            ]));

            return $this->successResponse($location, 'Ubicación creada correctamente', null, 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al crear la ubicación', null, $e);
        }
    }

    /**
     * Actualizar una ubicación
     *
     * Actualiza los campos de la ubicación indicada.
     *
     * **Parámetros de ruta:**
     * - `id` (integer, requerido): ID de la ubicación. Ejemplo: `1`.
     *
     * **Parámetros del body (JSON):** `name` (string, requerido, máximo 255),
     * `image` (string, requerido, máximo 2048; nombre de archivo o ruta bajo
     * `img/logos_ubicaciones/`), `address` y `mapsQuery` (string, requeridos,
     * máximo 500), `state` (boolean, requerido) y `sort_order` (integer, requerido,
     * mínimo 0).
     *
     * **Autenticación requerida:** Sí.
     *
     * ```bash
     * curl -X POST "http://localhost/api/locations/updateLocation/1" \
     *   -H "Accept: application/json" \
     *   -H "Content-Type: application/json" \
     *   -H "Authorization: Bearer {token}" \
     *   -d '{"name":"Universidad Don Bosco","image":"img/logos_ubicaciones/udb.png","address":"Campus Soyapango y Antiguo Cuscatlán","mapsQuery":"Universidad Don Bosco El Salvador","state":1,"sort_order":1}'
     * ```
     *
     * @authenticated
     *
     * @response {
     *   "ok": true,
     *   "message": "Ubicación actualizada correctamente",
     *   "data": {
     *     "idLocation": 1,
     *     "name": "Universidad Don Bosco",
     *     "image": "img/logos_ubicaciones/udb.png",
     *     "address": "Campus Soyapango y Antiguo Cuscatlán",
     *     "mapsQuery": "Universidad Don Bosco El Salvador",
     *     "state": 1,
     *     "sort_order": 1
     *   }
     * }
     *
     * @response 400 {
     *   "ok": false,
     *   "error": "Bad Request",
     *   "message": "El número de ID es requerido"
     * }
     *
     * @response 404 {
     *   "ok": false,
     *   "error": "Not Found",
     *   "message": "Ubicación no encontrada"
     * }
     *
     * @response 422 {
     *   "ok": false,
     *   "error": "Validation Error",
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "image": ["The image format is invalid."],
     *     "sort_order": ["The sort order field is required."]
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
            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El número de ID es requerido');
            }

            $location = EditorialLocation::findOrFail($id);
            $request->merge(['image' => $this->normalizeImagePath($request->input('image'))]);
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'image' => ['required', 'string', 'max:2048', 'regex:/^(?:[A-Za-z0-9_-][A-Za-z0-9._-]*|img\/logos_ubicaciones\/[A-Za-z0-9_-][A-Za-z0-9._-]*)$/'],
                'address' => 'required|string|max:500',
                'mapsQuery' => 'required|string|max:500',
                'state' => 'required|boolean',
                'sort_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $location->update($request->only([
                'name', 'image', 'address', 'mapsQuery', 'state', 'sort_order',
            ]));

            return $this->successResponse($location->fresh(), 'Ubicación actualizada correctamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new \App\Exceptions\NotFoundException('Ubicación no encontrada');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al actualizar la ubicación', null, $e);
        }
    }

    /**
     * Alternar estado de una ubicación
     *
     * Invierte el estado actual de la ubicación (activa/oculta).
     *
     * **Parámetros de ruta:**
     * - `id` (integer, requerido): ID de la ubicación. Ejemplo: `1`.
     *
     * **Autenticación requerida:** Sí.
     *
     * ```bash
     * curl -X POST "http://localhost/api/locations/toggleState/1" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer {token}"
     * ```
     *
     * @authenticated
     *
     * @response {
     *   "ok": true,
     *   "message": "Ubicación activada correctamente",
     *   "data": {
     *     "idLocation": 1,
     *     "name": "Universidad Don Bosco",
     *     "image": "img/logos_ubicaciones/udb.png",
     *     "state": 1,
     *     "sort_order": 1
     *   }
     * }
     *
     * @response 400 {
     *   "ok": false,
     *   "error": "Bad Request",
     *   "message": "El número de ID es requerido"
     * }
     *
     * @response 404 {
     *   "ok": false,
     *   "error": "Not Found",
     *   "message": "Ubicación no encontrada"
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    public function toggleState($id = null)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El número de ID es requerido');
            }

            $location = EditorialLocation::findOrFail($id);
            $location->state = !$location->state;
            $location->save();

            $message = $location->state
                ? 'Ubicación activada correctamente'
                : 'Ubicación ocultada correctamente';

            return $this->successResponse($location, $message);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new \App\Exceptions\NotFoundException('Ubicación no encontrada');
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al cambiar el estado de la ubicación', null, $e);
        }
    }

    /**
     * Subir una imagen de ubicación
     *
     * Guarda la imagen en `public/imageslocations` y devuelve el nombre generado.
     * El campo `name` se usa como prefijo del nombre del archivo.
     *
     * **Parámetros del formulario (multipart/form-data):**
     * - `file` (archivo, requerido): Imagen JPEG, JPG, PNG, GIF o WebP; máximo 2 MB.
     * - `name` (string, opcional, nullable): Nombre para el archivo, máximo 255 caracteres.
     *
     * **Autenticación requerida:** Sí.
     *
     * ```bash
     * curl -X POST "http://localhost/api/locations/uploadLocationImage" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer {token}" \
     *   -F "name=Universidad_Don_Bosco" \
     *   -F "file=@logo.png"
     * ```
     *
     * @authenticated
     *
     * @response {
     *   "ok": true,
     *   "message": "Logo subido correctamente",
     *   "data": {
     *     "filename": "Universidad_Don_Bosco_1792740022_6adb0ab638f95.png"
     *   }
     * }
     *
     * @response 422 {
     *   "ok": false,
     *   "error": "Validation Error",
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "file": ["The file field is required."]
     *   }
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    public function uploadImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
                'name' => 'string|nullable|max:255',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $file = $request->file('file');
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $request->input('name', 'ubicacion'));
            $fileName = $safeName . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $relativePath = 'imageslocations';
            $fullPath = public_path($relativePath);

            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            $file->move($fullPath, $fileName);

            return $this->successResponse([
                'filename' => $fileName,
            ], 'Logo subido correctamente');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al almacenar el logo de la ubicación', null, $e);
        }
    }

    private function normalizeImagePath($image)
    {
        if (!is_string($image)) {
            return $image;
        }

        $path = parse_url($image, PHP_URL_PATH);
        if (!is_string($path)) {
            return $image;
        }

        $path = ltrim($path, '/');
        $locationPathIndex = strpos($path, 'imageslocations/');

        return $locationPathIndex === false
            ? $path
            : substr($path, $locationPathIndex + strlen('imageslocations/'));
    }
}
