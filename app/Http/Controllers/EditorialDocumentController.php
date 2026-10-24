<?php

namespace App\Http\Controllers;

use App\EditorialDocument;
use App\Exceptions\BadRequestException;
use App\Exceptions\ServerErrorException;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\NormalizesMdxImages;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @resource Documentos editoriales
 *
 * Endpoints para consultar y administrar documentos editoriales.
 * Los endpoints protegidos usan JWT en el header: `Authorization: Bearer {token}`.
 */
class EditorialDocumentController extends Controller
{
    use ApiResponse;
    use NormalizesMdxImages;

    public function __construct()
    {
        $this->middleware('auth:api')->except(['active', 'findBySlug']);
    }

    /**
     * Listar documentos editoriales
     *
     * Obtiene todos los documentos, ordenados por `sort_order` y luego por `idDocument`.
     * Incluye documentos activos e inactivos.
     *
     * **Autenticación requerida:** Sí.
     *
     * ```bash
     * curl -X GET "http://localhost/api/documents/getDocuments" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer {token}"
     * ```
     *
     * @authenticated
     *
     * @response {
     *   "ok": true,
     *   "message": "Documentos obtenidos correctamente",
     *   "data": [
     *     {
     *       "idDocument": 1,
     *       "title": "Reglamento",
     *       "slug": "reglamento",
     *       "content": "Contenido del documento",
     *       "state": 1,
     *       "sort_order": 1,
     *       "created_at": "2026-10-22 20:36:23",
     *       "updated_at": "2026-10-22 20:36:23"
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
            $documents = EditorialDocument::orderBy('sort_order')
                ->orderBy('idDocument')
                ->get();

            return $this->successResponse($documents, 'Documentos obtenidos correctamente');
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener los documentos', null, $e);
        }
    }

    /**
     * Listar documentos activos
     *
     * Obtiene únicamente documentos activos. La respuesta incluye `idDocument`,
     * `title`, `slug`, `content` y `sort_order`, ordenados por `sort_order`.
     * Este endpoint es público.
     *
     * ```bash
     * curl -X GET "http://localhost/api/documents/getActiveDocuments" \
     *   -H "Accept: application/json"
     * ```
     *
     * @response {
     *   "ok": true,
     *   "message": "Documentos activos obtenidos correctamente",
     *   "data": [
     *     {
     *       "idDocument": 1,
     *       "title": "Reglamento",
     *       "slug": "reglamento",
     *       "content": "Contenido del documento",
     *       "sort_order": 1
     *     }
     *   ]
     * }
     */
    public function active()
    {
        try {
            $documents = EditorialDocument::where('state', 1)
                ->orderBy('sort_order')
                ->orderBy('idDocument')
                ->get(['idDocument', 'title', 'slug', 'content', 'sort_order']);

            return $this->successResponse($documents, 'Documentos activos obtenidos correctamente');
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener los documentos activos', null, $e);
        }
    }

    /**
     * Obtener un documento por ID
     *
     * Devuelve el documento correspondiente al identificador indicado, incluso si está inactivo.
     *
     * **Parámetros de ruta:**
     * - `id` (integer, requerido): ID del documento. Ejemplo: `1`.
     *
     * **Autenticación requerida:** Sí.
     *
     * ```bash
     * curl -X GET "http://localhost/api/documents/getDocument/1" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer {token}"
     * ```
     *
     * @authenticated
     *
     * @response {
     *   "ok": true,
     *   "message": "Documento obtenido correctamente",
     *   "data": {
     *     "idDocument": 1,
     *     "title": "Reglamento",
     *     "slug": "reglamento",
     *     "content": "Contenido del documento",
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
     *   "message": "Documento no encontrado"
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    public function find($id = null)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El número de ID es requerido');
            }

            $document = EditorialDocument::findOrFail($id);

            return $this->successResponse($document, 'Documento obtenido correctamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new \App\Exceptions\NotFoundException('Documento no encontrado');
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener el documento', null, $e);
        }
    }

    /**
     * Buscar un documento activo por slug
     *
     * Busca un documento por su slug. Solo devuelve documentos activos. Este endpoint es público.
     *
     * **Parámetros de ruta:**
     * - `slug` (string, requerido): Slug del documento. Ejemplo: `reglamento`.
     *
     * ```bash
     * curl -X GET "http://localhost/api/documents/searchDocument/reglamento" \
     *   -H "Accept: application/json"
     * ```
     *
     * @response {
     *   "ok": true,
     *   "message": "Documento obtenido correctamente",
     *   "data": {
     *     "idDocument": 1,
     *     "title": "Reglamento",
     *     "slug": "reglamento",
     *     "content": "Contenido del documento",
     *     "state": 1,
     *     "sort_order": 1
     *   }
     * }
     *
     * @response 400 {
     *   "ok": false,
     *   "error": "Bad Request",
     *   "message": "El slug del documento es requerido"
     * }
     *
     * @response 404 {
     *   "ok": false,
     *   "error": "Not Found",
     *   "message": "Documento no encontrado"
     * }
     */
    public function findBySlug($slug = null)
    {
        try {
            if (empty($slug)) {
                throw new BadRequestException('El slug del documento es requerido');
            }

            $document = EditorialDocument::where('slug', $slug)
                ->where('state', 1)
                ->first();

            if (!$document) {
                throw new \App\Exceptions\NotFoundException('Documento no encontrado');
            }

            return $this->successResponse($document, 'Documento obtenido correctamente');
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al buscar el documento', null, $e);
        }
    }

    /**
     * Crear un documento editorial
     *
     * Crea un documento nuevo. Las URLs de imágenes presentes en `content` se normalizan
     * antes de guardar el contenido.
     *
     * **Parámetros del body (JSON):**
     * - `title` (string, requerido): Título, máximo 255 caracteres.
     * - `slug` (string, requerido): Slug único, máximo 191 caracteres; solo letras, números,
     *   guiones y guiones bajos.
     * - `content` (string, opcional, nullable): Contenido del documento.
     * - `state` (boolean, opcional): Estado (0=inactivo, 1=activo). Por defecto: `1`.
     * - `sort_order` (integer, requerido): Orden de visualización, mínimo `0`.
     *
     * **Autenticación requerida:** Sí.
     *
     * ```bash
     * curl -X POST "http://localhost/api/documents/createDocument" \
     *   -H "Accept: application/json" \
     *   -H "Content-Type: application/json" \
     *   -H "Authorization: Bearer {token}" \
     *   -d '{"title":"Reglamento","slug":"reglamento","content":"Contenido","state":1,"sort_order":1}'
     * ```
     *
     * @authenticated
     *
     * @response 201 {
     *   "ok": true,
     *   "message": "Documento creado correctamente",
     *   "data": {
     *     "idDocument": 1,
     *     "title": "Reglamento",
     *     "slug": "reglamento",
     *     "content": "Contenido",
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
     *     "title": ["The title field is required."],
     *     "slug": ["The slug field is required."],
     *     "sort_order": ["The sort order field is required."]
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
                'title' => 'required|string|max:255',
                'slug' => 'required|string|max:191|alpha_dash|unique:editorial_documents,slug',
                'content' => 'string|nullable',
                'state' => 'boolean',
                'sort_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $document = EditorialDocument::create([
                'title' => $request->input('title'),
                'slug' => $request->input('slug'),
                'content' => $this->normalizeMdxImageUrls($request->input('content', '')),
                'state' => $request->input('state', 1),
                'sort_order' => $request->input('sort_order', 0),
            ]);

            return $this->successResponse($document, 'Documento creado correctamente', null, 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al crear el documento', null, $e);
        }
    }

    /**
     * Actualizar un documento editorial
     *
     * Actualiza todos los campos requeridos del documento indicado. El slug debe ser único,
     * excepto por el propio documento.
     *
     * **Parámetros de ruta:**
     * - `id` (integer, requerido): ID del documento. Ejemplo: `1`.
     *
     * **Parámetros del body (JSON):** `title` (string, requerido, máximo 255),
     * `slug` (string, requerido, único, máximo 191, formato alpha_dash), `content`
     * (string, opcional, nullable), `state` (boolean, requerido) y `sort_order`
     * (integer, requerido, mínimo 0).
     *
     * **Autenticación requerida:** Sí.
     *
     * ```bash
     * curl -X POST "http://localhost/api/documents/updateDocument/1" \
     *   -H "Accept: application/json" \
     *   -H "Content-Type: application/json" \
     *   -H "Authorization: Bearer {token}" \
     *   -d '{"title":"Reglamento actualizado","slug":"reglamento","content":"Contenido actualizado","state":1,"sort_order":1}'
     * ```
     *
     * @authenticated
     *
     * @response {
     *   "ok": true,
     *   "message": "Documento actualizado correctamente",
     *   "data": {
     *     "idDocument": 1,
     *     "title": "Reglamento actualizado",
     *     "slug": "reglamento",
     *     "content": "Contenido actualizado",
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
     *   "message": "Documento no encontrado"
     * }
     *
     * @response 422 {
     *   "ok": false,
     *   "error": "Validation Error",
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "slug": ["The slug has already been taken."],
     *     "state": ["The state field is required."]
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

            $document = EditorialDocument::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'slug' => 'required|string|max:191|alpha_dash|unique:editorial_documents,slug,' . $id . ',idDocument',
                'content' => 'string|nullable',
                'state' => 'required|boolean',
                'sort_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $documentData = $request->only(['title', 'slug', 'content', 'state', 'sort_order']);
            if (array_key_exists('content', $documentData)) {
                $documentData['content'] = $this->normalizeMdxImageUrls($documentData['content']);
            }
            $document->update($documentData);

            return $this->successResponse($document->fresh(), 'Documento actualizado correctamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new \App\Exceptions\NotFoundException('Documento no encontrado');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al actualizar el documento', null, $e);
        }
    }

    /**
     * Alternar estado de un documento
     *
     * Invierte el estado actual del documento (activo/inactivo).
     *
     * **Parámetros de ruta:**
     * - `id` (integer, requerido): ID del documento. Ejemplo: `1`.
     *
     * **Autenticación requerida:** Sí.
     *
     * ```bash
     * curl -X POST "http://localhost/api/documents/toggleState/1" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer {token}"
     * ```
     *
     * @authenticated
     *
     * @response {
     *   "ok": true,
     *   "message": "Documento activado correctamente",
     *   "data": {
     *     "idDocument": 1,
     *     "title": "Reglamento",
     *     "slug": "reglamento",
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
     *   "message": "Documento no encontrado"
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

            $document = EditorialDocument::findOrFail($id);
            $document->state = !$document->state;
            $document->save();

            $message = $document->state
                ? 'Documento activado correctamente'
                : 'Documento desactivado correctamente';

            return $this->successResponse($document, $message);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new \App\Exceptions\NotFoundException('Documento no encontrado');
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al cambiar el estado del documento', null, $e);
        }
    }

    /**
     * Subir una imagen para el contenido de un documento
     *
     * Guarda la imagen en `public/imagescontent` y devuelve el nombre generado.
     *
     * **Parámetros del formulario (multipart/form-data):**
     * - `file` (archivo, requerido): Imagen JPEG, JPG, PNG, GIF o WebP; máximo 2 MB.
     * - `title` (string, requerido): Texto usado para generar el nombre del archivo,
     *   máximo 100 caracteres.
     *
     * **Autenticación requerida:** Sí.
     *
     * ```bash
     * curl -X POST "http://localhost/api/documents/uploadDocumentImage" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer {token}" \
     *   -F "title=Reglamento" \
     *   -F "file=@imagen.png"
     * ```
     *
     * @authenticated
     *
     * @response {
     *   "ok": true,
     *   "message": "Imagen subida correctamente",
     *   "data": {
     *     "filename": "Reglamento_1792740022_6adb0ab638f95.png"
     *   }
     * }
     *
     * @response 422 {
     *   "ok": false,
     *   "error": "Validation Error",
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "file": ["The file field is required."],
     *     "title": ["The title field is required."]
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
                'title' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $file = $request->file('file');
            $safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $request->input('title'));
            $fileName = $safeTitle . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $relativePath = 'imagescontent';
            $fullPath = public_path($relativePath);

            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            $file->move($fullPath, $fileName);

            return $this->successResponse([
                'filename' => $fileName,
            ], 'Imagen subida correctamente');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al almacenar la imagen del documento', null, $e);
        }
    }
}
