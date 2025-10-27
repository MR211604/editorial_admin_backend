<?php

namespace App\Http\Controllers;

use App\Http\Requests\Publishing\PublishingIndexRequest;
use App\Http\Requests\Publishing\PublishingShowRequest;
use App\Http\Requests\Publishing\PublishingStoreRequest;
use App\Http\Requests\Publishing\PublishingToggleStatusRequest;
use App\Http\Requests\Publishing\PublishingUpdateRequest;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\SearchFilter;
use DB;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerErrorException;
use Illuminate\Http\Request;
use App\Publishing;
use Illuminate\Support\Facades\Validator;

/**
 * @resource Publicaciones
 *
 * Endpoints para gestionar publicaciones (libros, revistas y otros materiales).
 */
class PublishingController extends Controller
{

    use ApiResponse;
    use SearchFilter;
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    private function deleteCoverImage($coverPath)
    {
        try {
            if ($coverPath) {
                $fullPath = public_path($coverPath);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
        } catch (Exception $e) {
            // Log del error pero no detener la ejecución
            \Log::warning("No se pudo eliminar la imagen: {$coverPath}");
        }
    }

    private function sanitizeFileName($name)
    {
        // Convertir a minúsculas
        $name = mb_strtolower($name, 'UTF-8');

        // Reemplazar caracteres especiales
        $name = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', ' ', '/', '\\', '&'],
            ['a', 'e', 'i', 'o', 'u', 'n', '_', '_', '_', 'y'],
            $name
        );

        // Eliminar caracteres no permitidos
        $name = preg_replace('/[^a-z0-9_-]/', '', $name);

        // Limitar longitud
        return substr($name, 0, 50);
    }

    private function storeCoverImage($file, $publishingType, $categoryId, $title)
    {
        try {
            // Determinar tipo de publicación
            $typeFolder = $publishingType == 1 ? 'libros' : 'revistas';

            // Obtener nombre de categoría
            $categoryFolder = 'sin_categoria';
            if ($categoryId) {
                $category = \App\Category::find($categoryId);
                if ($category) {
                    $categoryFolder = $this->sanitizeFileName($category->title);
                }
            }

            // Sanitizar nombre de la publicación
            $titleFolder = $this->sanitizeFileName($title);

            // Generar nombre único para el archivo
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . uniqid() . '.' . $extension;

            // Ruta completa: editorial_files/libros|revistas/categoria/titulo/imagen.jpg
            $relativePath = "editorial_files/{$typeFolder}/{$categoryFolder}";
            $fullPath = public_path($relativePath);

            // Crear directorio si no existe
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            // Mover archivo
            $file->move($fullPath, $fileName);

            // Retornar ruta relativa para guardar en BD
            return "{$relativePath}/{$fileName}";

        } catch (Exception $e) {
            throw new ServerErrorException('Error al almacenar la imagen', null, $e);
        }
    }
 /**
     * Obtener lista de publicaciones.
     *
     * Devuelve una lista paginada de publicaciones con opciones de filtrado por
     * tipo, categoría y término de búsqueda, así como ordenamiento dinámico.
     *
     * @group Publicaciones
     * @authenticated
     * @queryParam page integer Número de página a recuperar. Example: 1
     * @queryParam limit integer Cantidad de resultados por página (mínimo 10). Example: 10
     * @queryParam search string Texto a buscar en el título. Example: literatura
     * @queryParam category integer ID de la categoría por la que se filtrará. Example: 3
     * @queryParam publishing_type integer ID del tipo de publicación (1=libro, 2=revista, etc.). Example: 1
     * @queryParam sort string Campo y dirección de orden (id,title,created_at,updated_at,publishing_type; usa '-' para descendente). Example: -created_at
     * Posibles errores:
     * - 422 Parámetros inválidos (ej. `publishing_type` no entero).
     * - 500 Error interno al recuperar las publicaciones.
     *
     * @response {
     *   "ok": true,
     *   "message": "Publicaciones obtenidas correctamente",
     *   "data": [
     *     {
     *       "id": 272,
     *       "title": "Luna de pluton",
     *       "cover": null,
     *       "age": null,
     *       "volume": null,
     *       "volume_date": null,
     *       "publish_code": "2112313333",
     *       "is_new_arrival": 1,
     *       "publish_date": "2023-02-05",
     *       "state": null,
     *       "publisher": null,
     *       "stock": null,
     *       "author_id": null,
     *       "language_id": null,
     *       "category_id": 8,
     *       "publishing_type": 1,
     *       "age_number": null,
     *       "pages": 50,
     *       "URL": "milibrolunadepluton",
     *       "is_archived": 0,
     *       "type": {
     *         "id": 1,
     *         "name": "Libro",
     *         "description": "Publicacion de tipo libro, contiene autor"
     *       },
     *       "authors": [
     *         {
     *           "id": 113,
     *           "name": "Dross",
     *           "lastname": "Rotzank",
     *           "pivot": {
     *             "publishing_id": 272,
     *             "author_id": 113
     *           }
     *         }
     *       ],
     *       "details": []
     *     }
     *   ],
     *   "pagination": {
     *     "total": 25,
     *     "current_page": 1,
     *     "last_page": 3,
     *     "per_page": 10,
     *     "from": 1,
     *     "to": 10
     *   }
     * }
     */
    public function index(PublishingIndexRequest $request)
    {
        try {
            $allowedSortFields = ['id', 'title', 'created_at', 'updated_at', 'publishing_type'];
            // $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            $publishingType = $request->query('publishing_type', null);
            $search = $request->query('search', null);
            $category = $request->query('category', null);
            $isArchived = $request->has('is_archived') ? (int) $request->query('is_archived') : null;

            $query = Publishing::with(['type', 'authors', 'details']);

            if ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            }

            if ($isArchived !== null) {
                $query->where('is_archived', $isArchived);
            }

            if ($category) {
                $query->where('category_id', $category);
            }

            if ($publishingType) {
                $query->where('publishing_type', $publishingType);
            }

            if ($request->has('sort')) {
                $this->search($request, $query, $allowedSortFields);
            } else {
                // Ordenamiento por defecto
                $query->orderBy('id', 'desc');
            }

            $publishings = $query->paginate($limit);

            $pagination = [
                'total' => $publishings->total(),
                'current_page' => $publishings->currentPage(),
                'last_page' => $publishings->lastPage(),
                'per_page' => $publishings->perPage(),
                'from' => $publishings->firstItem(),
                'to' => $publishings->lastItem(),
            ];

            return $this->successResponse($publishings->items(), 'Publicaciones obtenidas correctamente', $pagination, 200);

        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener las publicaciones', null, $e);
        }
    }
    /**
     * Obtener una publicación por ID.
     *
     * Recupera la información completa de una publicación específica, incluyendo
     * su tipo, autores y detalles asociados.
     *
     * @group Publicaciones
     * @authenticated
     * @urlParam id integer required ID de la publicación a consultar. Example: 12
     * Posibles errores:
     * - 422 El número de ID es requerido o no es válido.
     * - 404 No se encontró la publicación solicitada.
     * - 500 Error interno al recuperar la publicación.
     *
     * @response {
     *   "ok": true,
     *   "message": "Publicación obtenida exitosamente",
     *   "data": {
     *     "id": 1,
     *     "title": "Manual de laboratorio, Instalaciones de climatización",
     *     "cover": null,
     *     "age": null,
     *     "volume": null,
     *     "volume_date": null,
     *     "publish_code": null,
     *     "is_new_arrival": 0,
     *     "publish_date": null,
     *     "state": 0,
     *     "publisher": "Editorial Universidad Don Bosco",
     *     "stock": 62,
     *     "author_id": null,
     *     "language_id": 1,
     *     "category_id": null,
     *     "publishing_type": 3,
     *     "age_number": null,
     *     "pages": null,
     *     "URL": null,
     *     "is_archived": 0,
     *     "type": {
     *       "id": 3,
     *       "name": "Manual",
     *       "description": "Publicacion de tipo manual, contiene categoria"
     *     },
     *     "authors": [],
     *     "details": []
     *   },
     *   "pagination": null
     * }
     */

    public function find(PublishingShowRequest $request, $id = null)
    {
        try {
            $publishing = Publishing::with(['type', 'authors', 'details'])->findOrFail($request->route('id'));

            return $this->successResponse($publishing, 'Publicación obtenida exitosamente');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new NotFoundException('Publicación no encontrada');
        } catch (\App\Exceptions\ApiException $e) {
            // Dejar que las excepciones de API lleguen al Handler
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener la publicación', null, $e);
        }
    }
/**
     * Crear una nueva publicación.
     *
     * Registra una publicación con datos básicos y la asocia a un tipo y, de ser
     * necesario, a una categoría.
     *
     * Cuerpo JSON esperado:
     * ```json
     * {
     *   "title": "Introducción a Laravel",
     *   "URL": "https://example.com/laravel",
     *   "category_id": 4,
     *   "publishing_type": 1
     * }
     * ```
     *
     * @group Publicaciones
     * @authenticated
     * @bodyParam title string required Título de la publicación. Example: Introducción a Laravel
     * @bodyParam URL string URL opcional con información adicional. Example: https://example.com/laravel
     * @bodyParam category_id integer ID de la categoría; debe existir en `categories`. Example: 4
     * @bodyParam publishing_type integer required ID del tipo de publicación; debe existir en `publishing_types`. Example: 1
     * Posibles errores:
     * - 422 Parámetros inválidos (por ejemplo, falta `title`).
     * - 500 Error interno al crear la publicación.
     *
     * @response {
     *   "ok": true,
     *   "message": "Publicacion creada exitosamente",
     *   "data": {
     *     "title": "Nueva Revista1",
     *     "URL": "https://revistas/1",
     *     "category_id": 4,
     *     "age": null,
     *     "volume": null,
     *     "publish_code": null,
     *     "is_new_arrival": 0,
     *     "publish_date": null,
     *     "publishing_type": 1,
     *     "age_number": null,
     *     "pages": null,
     *     "id": 275
     *   },
     *   "pagination": null
     * }
     */
    public function create(PublishingStoreRequest $request)
    {
        try {
            $publishing = Publishing::create([
                'title' => $request->input('title'),
                'URL' => $request->input('URL'),
                'category_id' => $request->input('category_id'),
                'cover' => null,
                'age' => null,
                'volume' => null,
                'volume_date' => null,
                'publish_code' => null,
                'is_new_arrival' => 0,
                'publish_date' => null,
                'publisher' => null,
                'stock' => null,
                'author_id' => null,
                'language_id' => null,
                'publishing_type' => $request->input('publishing_type'),
                'age_number' => null,
                'pages' => null,
            ]);

            return $this->successResponse($publishing, 'Publicacion creada exitosamente', null, 200);

        } catch (\App\Exceptions\ApiException $e) {
            // Dejar que las excepciones de API lleguen al Handler
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al crear la publicación', null, $e);
        }
    }
 /**
     * Actualizar una publicación existente.
     *
     * Permite modificar los datos de una publicación. Las reglas de validación
     * varían según el tipo de publicación (`publishing_type`). Para libros (1)
     * y revistas (2) se requieren campos específicos, y siempre se puede
     * sincronizar la relación con autores.
     *
     * Cuerpo JSON esperado (libro):
     * ```json
     *{
     *  "publishing_type": 1,
     *  "title": "Clean Architecture",
    *   "pages":"15",
    *   "publish_date": "2020-05-10",
    *   "publish_code": 123456,
    *   "category_id": {{category_id}},
    *   "is_new_arrival": true,
    *   "URL": "https://mi.biblio/libros/clean-architecture",
    *   "authors": [1,2]
    *}
     * ```
     *
     * Cuerpo JSON esperado (revista):
     * ```json
     * {
     *   "publishing_type": 2,
     *   "title": "Revista Tech",
     *   "pages": 48,
     *   "publish_date": "2024-08-01",
     *   "publish_code": 551,
     *   "age": 12,
     *   "age_number": 4,
     *   "volume": 3,
     *   "is_new_arrival": false,
     *   "category_id": 5,
     *   "authors": [7]
     * }
     * ```
     *
     * @group Publicaciones
     * @authenticated
     * @urlParam id integer required ID de la publicación a actualizar. Example: 12
     * @bodyParam publishing_type integer required Tipo de publicación que se actualizará. Example: 1
     * @bodyParam title string required Nuevo título de la publicación. Example: Introducción a Laravel
     * @bodyParam pages integer Cantidad de páginas (requerido para libros y revistas). Example: 320
     * @bodyParam publish_date date Fecha de publicación. Example: 2024-07-15
     * @bodyParam publish_code integer Código de publicación, como ISBN o folio. Example: 1024
     * @bodyParam category_id integer ID de la categoría; debe existir en `categories`. Example: 4
     * @bodyParam is_new_arrival boolean Indica si es una novedad (true/false). Example: true
     * @bodyParam URL string URL opcional relacionada a la publicación. Example: https://example.com/laravel
     * @bodyParam authors array Lista de IDs de autores asociados. Example: [7,9]
     * @bodyParam age integer Número de edición (solo revistas). Example: 12
     * @bodyParam age_number integer Número de entrega (solo revistas). Example: 4
     * @bodyParam volume integer Volumen o tomo (solo revistas). Example: 3
     * Posibles errores:
     * - 400 El número de ID es requerido o no es válido.
     * - 404 No se encontró la publicación solicitada o no coincide con el tipo indicado.
     * - 422 Parámetros inválidos (por ejemplo, `pages` no es entero).
     * - 500 Error interno al actualizar la publicación.
     *
     * @response {
     *   "ok": true,
     *   "message": "Publicación actualizada exitosamente",
     *   "data": {
     *     "id": 276,
     *     "title": "Clean Architecture",
     *     "cover": null,
     *     "age": null,
     *     "volume": null,
     *     "volume_date": null,
     *     "publish_code": 123456,
     *     "is_new_arrival": 1,
     *     "publish_date": "2020-05-10",
     *     "state": null,
     *     "publisher": null,
     *     "stock": null,
     *     "author_id": null,
     *     "language_id": null,
     *     "category_id": 1,
     *     "publishing_type": 1,
     *     "age_number": null,
     *     "pages": 15,
     *     "URL": "https://mi.biblio/libros/clean-architecture",
     *     "is_archived": 0,
     *     "authors": [],
     *     "category": {
     *       "id": 1,
     *       "title": "Cientifica",
     *       "description": "Categoria de libros cientificos",
     *       "main_category": null,
     *       "state": 0,
     *       "is_featured": 0,
     *       "language_id": 1,
     *       "category_type": 1
     *     },
     *     "type": {
     *       "id": 1,
     *       "name": "Libro",
     *       "description": "Publicacion de tipo libro, contiene autor"
     *     },
     *     "details": []
     *   },
     *   "pagination": null
     * }
     */
    public function update(PublishingUpdateRequest $request, $id = null)
    {

        try {

            if ($id === null) {
                $id = $request->route('id');
            }
            $updatedPublishing = Publishing::with(['authors', 'category', 'type', 'details'])->findOrFail($id);
            $publishingType = (int) $request->input('publishing_type');

            //! Operacion para los libros
            if ($publishingType === 1) {
                if (!Publishing::where('id', $id)->where('publishing_type', 1)->exists()) {
                    throw new NotFoundException('Publicación no encontrada');
                }

                DB::transaction(function () use ($id, $request) {
                    // Buscar la publicación con algunas relaciones
                    $publishing = Publishing::with(['category', 'type', 'details'])->findOrFail($id);

                    if ($request->hasFile('cover')) {
                        // Eliminar imagen anterior si existe
                        if ($publishing->cover) {
                            $this->deleteCoverImage($publishing->cover);
                        }
                        $coverPath = $this->storeCoverImage(
                            $request->file('cover'),
                            $request->input('publishing_type'),
                            $request->input('category_id'),
                            $request->input('title')
                        );

                        $publishing->cover = $coverPath;
                    }

                    // Actualizar atributos principales
                    $publishing->update($request->only([
                        'publishing_type',
                        'title',
                        'pages',
                        'publish_date',
                        'publish_code',
                        'category_id',
                        'is_new_arrival',
                        'URL'
                    ]));

                    // Sincronizar autores
                    $publishing->authors()->sync($request->input('authors'));
                });

            }

            //? Operacion para las revistas
            if ($publishingType === 2) {

                //Esta validacion asegura que la publicación sea del tipo revista
                if (!Publishing::where('id', $id)->where('publishing_type', 2)->exists()) {
                    throw new NotFoundException('Publicación no encontrada');
                }

                // Usar transacción para asegurar integridad de datos
                DB::transaction(function () use ($id, $request) {
                    // Buscar la publicación con algunas relaciones
                    $publishing = Publishing::with(['category', 'type', 'details'])->findOrFail($id);

                    if ($request->hasFile('cover')) {
                        // Eliminar imagen anterior si existe
                        if ($publishing->cover) {
                            $this->deleteCoverImage($publishing->cover);
                        }

                        $coverPath = $this->storeCoverImage(
                            $request->file('cover'),
                            $request->input('publishing_type'),
                            $request->input('category_id'),
                            $request->input('title')
                        );

                        $publishing->cover = $coverPath;
                    }

                    // Actualizar atributos principales
                    $publishing->update($request->only([
                        'title',
                        'publish_date',
                        'publish_code',
                        'age',
                        'age_number',
                        'pages',
                        'volume',
                        'is_new_arrival',
                        'category_id',
                        'publishing_type'
                    ]));

                    // Sincronizar autores
                    $publishing->authors()->sync($request->input('authors'));
                });
            }

            return $this->successResponse($updatedPublishing, 'Publicación actualizada exitosamente', null, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new NotFoundException('Publicación no encontrada');
        } catch (\App\Exceptions\ApiException $e) {
            // Dejar que las excepciones de API lleguen al Handler
            throw $e;
        } catch (\Exception $e) {
            throw new ServerErrorException('Error al actualizar la publicación', null, $e);
        }
    }

    /**
     * Cambiar estado de archivo
     *
     * Marca una publicación como archivada o activa.
     *
     * @group Publicaciones
     * @authenticated
     * @urlParam id integer required ID de la publicación a actualizar. Example: 15
     * @bodyParam is_archived boolean required Define si la publicación queda archivada (`true`) o activa (`false`). Example: true
     *
     * @response {
     *   "ok": true,
     *   "message": "Publicación archivada exitosamente",
     *   "data": null,
     *   "pagination": null
     * }
     */
    public function toggleStatus(PublishingToggleStatusRequest $request, $id = null)
    {
        try {

            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El ID de publicación es requerido');
            }

            $publishing = Publishing::findOrFail($id);
            $updatedStatus = filter_var($request->input('is_archived'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($updatedStatus === null) {
                throw new BadRequestException('El valor de is_archived no es válido');
            }

            $publishing->update(['is_archived' => $updatedStatus]);

            $message = $updatedStatus ? 'Publicación archivada exitosamente' : 'Publicación reactivada exitosamente';

            return $this->successResponse(null, $message, null, 200);

        } catch (Exception $e) {
            throw new ServerErrorException('Error al actualizar el estado de la publicación', null, $e);
        }
    }

    /**
     * Resumen de publicaciones
     *
     * Devuelve estadísticas básicas para el dashboard (cantidad de libros y revistas).
     *
     * @group Publicaciones
     * @authenticated
     *
     * @response {
     *   "ok": true,
     *   "message": "Resumen del dashboard obtenido correctamente",
     *   "data": {
     *     "books": 150,
     *     "magazines": 61
     *   },
     *   "pagination": null
     * }
     */
    public function summary()
    {
        try {
            $booksCount = Publishing::where('publishing_type', 1)->count();
            $magazinesCount = Publishing::where('publishing_type', 2)->count();

            $data = [
                'books' => $booksCount,
                'magazines' => $magazinesCount,
            ];

            return $this->successResponse($data, 'Resumen del dashboard obtenido correctamente', null, 200);
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener el resumen del dashboard', null, $e);
        }
    }

    /**
     * Eliminar publicación
     *
     * Elimina definitivamente una publicación archivada (y sus relaciones).
     *
     * @group Publicaciones
     * @authenticated
     * @urlParam id integer required ID de la publicación a eliminar (debe estar archivada). Example: 18
     *
     * @response {
     *   "ok": true,
     *   "message": "Publicación eliminada exitosamente",
     *   "data": null,
     *   "pagination": null
     * }
     */
    public function delete($id = null)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El ID de publicación es requerido');
            }

            $publishing = Publishing::findOrFail($id);

            //Si el campo is_archived es 0, no se puede eliminar
            if ($publishing->is_archived == 0) {
                throw new BadRequestException('La publicación debe estar archivada para poder eliminarla');
            }

            DB::transaction(function () use ($publishing) {

                if ($publishing->cover) {
                    $this->deleteCoverImage($publishing->cover);
                }

                $publishing->authors()->detach();
                $publishing->details()->delete();
                $publishing->delete();
            });

            return $this->successResponse(null, 'Publicación eliminada exitosamente', null, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new NotFoundException('Publicación no encontrada');
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al eliminar la publicación', null, $e);
        }
    }

}
