<?php

namespace App\Http\Controllers;

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
 * @resource Publishings
 *
 * Endpoints para la gestión de publicaciones (libros y revistas).
 * Usa JWT en el header: `Authorization: Bearer {token}`.
 */
class PublishingController extends Controller
{

    use ApiResponse;
    use SearchFilter;

    /**
     * Constructor (middleware)
     *
     * Aplica el middleware `auth:api` a todas las rutas.
     */
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
            throw new ServerErrorException('Error al almacenar la imagen', $e->getMessage(), $e);
        }
    }

    /**
     * Listar publicaciones
     * 
     * Obtiene una lista paginada de publicaciones (libros y revistas) con opciones de filtrado, búsqueda y ordenamiento.
     * 
     * **Autenticación requerida:**
     * Este endpoint requiere un token JWT válido en el header de autorización.
     * 
     * **Parámetros de consulta (Query Parameters):**
     * - `page` (integer, opcional): Número de página. Por defecto: 1. Ejemplo: `1`
     * - `limit` (integer, opcional): Resultados por página (mínimo 10). Por defecto: 10. Ejemplo: `10`
     * - `publishing_type` (integer, opcional): Tipo de publicación (1=Libro, 2=Revista). Ejemplo: `1`
     * - `language_id` (integer, opcional): ID del idioma para las traducciones. Por defecto: 1. Ejemplo: `1`
     * - `search` (string, opcional): Búsqueda por título. Ejemplo: `Laravel`
     * - `category` (integer, opcional): Filtrar por ID de categoría. Ejemplo: `3`
     * - `is_archived` (integer, opcional): Filtrar por estado (0=Activo, 1=Archivado). Ejemplo: `0`
     * - `sort` (string, opcional): Campo para ordenar (id, title, created_at, updated_at, publishing_type). Ejemplo: `title:asc`
     * 
     * 
     * ```bash
     * curl -X GET "http://localhost/api/publishings/getPublishings?page=1&limit=10&publishing_type=1&search=Laravel" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
     * ```
     * 
     * @authenticated
     * 
     * @response {
     *   "ok": true,
     *   "message": "Publicaciones obtenidas correctamente",
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Laravel para Principiantes",
     *       "cover": "editorial_files/libros/programacion/laravel_principiantes/1234567890.jpg",
     *       "pages": 320,
     *       "publish_date": "2025-01-15",
     *       "publish_code": "978-84-9876-123-4",
     *       "category_id": 3,
     *       "publishing_type": 1,
     *       "is_new_arrival": true,
     *       "is_archived": false,
     *       "created_at": "2025-11-15 10:30:00",
     *       "updated_at": "2025-11-15 10:30:00",
     *       "type": {
     *         "id": 1,
     *         "name": "Libro"
     *       },
     *       "authors": [
     *         {
     *           "id": 1,
     *           "name": "Juan",
     *           "lastname": "García"
     *         }
     *       ],
     *       "translation": {
     *         "id": 1,
     *         "title": "Laravel para Principiantes",
     *         "URL": "laravel-para-principiantes",
     *         "language_id": 1
     *       }
     *     }
     *   ],
     *   "pagination": {
     *     "total": 50,
     *     "current_page": 1,
     *     "last_page": 5,
     *     "per_page": 10,
     *     "from": 1,
     *     "to": 10
     *   }
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * 
     * @response 422 {
     *   "ok": false,
     *   "error": "Validation Error",
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "page": ["The page must be at least 1."],
     *     "publishing_type": ["The selected publishing type is invalid."]
     *   }
     * }
     */
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'limit' => 'integer|min:10',
                'publishing_type' => 'integer',
                'language_id' => 'integer|exists:languages,id',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $allowedSortFields = ['id', 'title', 'created_at', 'updated_at', 'publishing_type'];

            $limit = $request->query('limit', 10);
            $publishingType = $request->query('publishing_type', null);
            $languageId = $request->query('language_id', 1);
            $search = $request->query('search', null);
            $category = $request->query('category', null);
            $isArchived = $request->has('is_archived') ? (int) $request->query('is_archived') : null;

            $query = Publishing::with([
                'type',
                'authors',
                'details',
                'translation' => function ($q) use ($languageId) {
                    $q->where('language_id', $languageId);
                }
            ]);

            // if ($search) {
            //     $query->where('title', 'like', '%' . $search . '%');
            // }
            if ($search) {
                $query->whereHas('translations', function ($q) use ($search, $languageId) {
                    $q->where('language_id', $languageId)
                        ->where('title', 'like', '%' . $search . '%');
                });
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

        } catch (ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener las publicaciones', null, $e);
        }
    }

    /**
     * Obtener una publicación
     * 
     * Obtiene la información detallada de una publicación específica con todas sus relaciones (tipo, autores, detalles, traducciones).
     * 
     * **Autenticación requerida:**
     * Este endpoint requiere un token JWT válido en el header de autorización.
     * 
     * **Parámetros de ruta (URL Parameters):**
     * - `id` (integer, requerido): ID de la publicación a consultar. Ejemplo: `1`
     * 
     * ```bash
     * curl -X GET "http://localhost/api/publishings/getPublishing/1" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
     * ```
     * 
     * @authenticated
     * 
     * @response {
     *   "ok": true,
     *   "message": "Publicación obtenida exitosamente",
     *   "data": {
     *     "id": 1,
     *     "title": "Laravel para Principiantes",
     *     "cover": "editorial_files/libros/programacion/laravel_principiantes/1234567890.jpg",
     *     "pages": 320,
     *     "publish_date": "2025-01-15",
     *     "publish_code": "978-84-9876-123-4",
     *     "category_id": 3,
     *     "publishing_type": 1,
     *     "is_new_arrival": true,
     *     "is_archived": false,
     *     "type": {
     *       "id": 1,
     *       "name": "Libro"
     *     },
     *     "authors": [
     *       {
     *         "id": 1,
     *         "name": "Juan",
     *         "lastname": "García"
     *       }
     *     ],
     *     "translations": [
     *       {
     *         "id": 1,
     *         "title": "Laravel para Principiantes",
     *         "URL": "laravel-para-principiantes",
     *         "language": {
     *           "id": 1,
     *           "name": "Español"
     *         }
     *       }
     *     ]
     *   }
     * }
     * 
     * @response 400 {
     *   "ok": false,
     *   "error": "Bad Request",
     *   "message": "El numero de ID es requerido"
     * }
     * 
     * @response 404 {
     *   "ok": false,
     *   "error": "Not Found",
     *   "message": "Publicación no encontrada"
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
                throw new BadRequestException('El numero de ID es requerido');
            }

            if (!Publishing::where('id', $id)->exists()) {
                throw new NotFoundException('Publicación no encontrada');
            }

            $publishing = Publishing::with(['type', 'authors', 'details', 'translations.language'])->findOrFail($id);

            return $this->successResponse($publishing, 'Publicación obtenida exitosamente');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new NotFoundException('Publicación no encontrada');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            // Dejar que las excepciones de API lleguen al Handler
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener la publicación', null, $e);
        }
    }

    /**
     * Crear una publicación
     * 
     * Crea una nueva publicación (libro o revista) con sus traducciones en múltiples idiomas.
     * Esta es una operación inicial que crea la publicación base, luego debe actualizarse con más detalles.
     * 
     * **Autenticación requerida:**
     * Este endpoint requiere un token JWT válido en el header de autorización.
     * 
     * **Parámetros del Body:**
     * - `title` (string, requerido): Título principal de la publicación (máximo 255 caracteres). Ejemplo: `Laravel para Principiantes`
     * - `URL` (string, opcional): URL slug de la publicación. Ejemplo: `laravel-para-principiantes`
     * - `category_id` (integer, opcional): ID de la categoría. Ejemplo: `3`
     * - `publishing_type` (integer, requerido): Tipo de publicación (1=Libro, 2=Revista). Ejemplo: `1`
     * - `translations` (array, requerido): Array de traducciones (mínimo 1)
     * - `translations[].language_id` (integer, requerido): ID del idioma. Ejemplo: `1`
     * - `translations[].title` (string, requerido): Título traducido (máximo 255 caracteres). Ejemplo: `Laravel for Beginners`
     * - `translations[].URL` (string, opcional): URL slug traducida. Ejemplo: `laravel-for-beginners`
     * 
     * ```json
     * {
     *   "title": "Laravel para Principiantes",
     *   "URL": "laravel-para-principiantes",
     *   "category_id": 3,
     *   "publishing_type": 1,
     *   "translations": [
     *     {
     *       "language_id": 1,
     *       "title": "Laravel para Principiantes",
     *       "URL": "laravel-para-principiantes"
     *     },
     *     {
     *       "language_id": 2,
     *       "title": "Laravel for Beginners",
     *       "URL": "laravel-for-beginners"
     *     }
     *   ]
     * }
     * ```
     * 
     * ```bash
     * curl -X POST "http://localhost/api/publishings/createPublishing" \
     *   -H "Accept: application/json" \
     *   -H "Content-Type: application/json" \
     *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
     *   -d '{"title":"Laravel para Principiantes","publishing_type":1,"translations":[{"language_id":1,"title":"Laravel para Principiantes"}]}'
     * ```
     * 
     * @authenticated
     * 
     * @response {
     *   "ok": true,
     *   "message": "Publicacion creada exitosamente",
     *   "data": {
     *     "id": 1,
     *     "title": "Laravel para Principiantes",
     *     "URL": "laravel-para-principiantes",
     *     "category_id": 3,
     *     "publishing_type": 1,
     *     "created_at": "2025-11-15 10:30:00",
     *     "updated_at": "2025-11-15 10:30:00"
     *   }
     * }
     * 
     * @response 422 {
     *   "ok": false,
     *   "error": "Validation Error",
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "title": ["The title field is required."],
     *     "publishing_type": ["The selected publishing type is invalid."],
     *     "translations": ["The translations field is required."]
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
                'URL' => 'nullable|string',
                'category_id' => 'nullable|integer|exists:categories,id',
                'publishing_type' => 'required|integer|exists:publishing_types,id',

                // Traducciones
                'translations' => 'required|array|min:1',
                'translations.*.language_id' => 'required|integer|exists:languages,id',
                'translations.*.title' => 'required|string|max:255',
                'translations.*.URL' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            DB::transaction(function () use ($request, &$publishing) {
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
                    // 'language_id' => null,
                    'publishing_type' => $request->input('publishing_type'),
                    'age_number' => null,
                    'pages' => null,
                ]);
                foreach ($request->input('translations') as $translation) {
                    $publishing->translations()->create([
                        'language_id' => $translation['language_id'],
                        'title' => $translation['title'],
                        'URL' => $translation['URL'] ? $translation['URL'] : null,
                    ]);
                }
            });
            return $this->successResponse($publishing, 'Publicacion creada exitosamente', null, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            // Dejar que las excepciones de API lleguen al Handler
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al crear la publicación', $e->getMessage(), $e);
        }
    }

    /**
     * Actualizar una publicación
     * 
     * Actualiza una publicación existente. Los campos requeridos varían según el tipo de publicación:
     * - **Libros (publishing_type=1)**: Requiere páginas, fecha de publicación, código ISBN, y puede incluir portada
     * - **Revistas (publishing_type=2)**: Además de lo anterior, puede incluir año, número, volumen
     * 
     * **Autenticación requerida:**
     * Este endpoint requiere un token JWT válido en el header de autorización.
     * 
     * **Parámetros de ruta (URL Parameters):**
     * - `id` (integer, requerido): ID de la publicación a actualizar. Ejemplo: `1`
     * 
     * **Parámetros del Body (para Libros - publishing_type=1):**
     * - `publishing_type` (integer, requerido): Debe ser 1 para libros
     * - `pages` (integer, requerido): Número de páginas. Ejemplo: `320`
     * - `publish_date` (date, requerido): Fecha de publicación. Ejemplo: `2025-01-15`
     * - `publish_code` (string, requerido): Código ISBN (formato: XXX-XX-XXXX-XXX-X). Ejemplo: `978-84-9876-123-4`
     * - `category_id` (integer, opcional): ID de categoría. Ejemplo: `3`
     * - `is_new_arrival` (boolean, opcional): ¿Es novedad? Ejemplo: `true`
     * - `translations` (array, requerido): Array de traducciones
     * - `translations[].language_id` (integer, requerido): ID del idioma
     * - `translations[].title` (string, requerido): Título traducido
     * - `translations[].URL` (string, opcional): URL slug
     * - `authors` (array, opcional): Array de IDs de autores. Ejemplo: `[1, 2]`
     * - `cover` (file, opcional): Imagen de portada (JPEG, PNG)
     * 
     * **Parámetros del Body (para Revistas - publishing_type=2):**
     * - `publishing_type` (integer, requerido): Debe ser 2 para revistas
     * - `pages` (integer, requerido): Número de páginas
     * - `publish_date` (date, requerido): Fecha de publicación
     * - `publish_code` (string, requerido): Código ISSN
     * - `age` (integer, opcional): Año de la revista. Ejemplo: `2025`
     * - `age_number` (integer, opcional): Número dentro del año. Ejemplo: `3`
     * - `volume` (integer, opcional): Volumen. Ejemplo: `12`
     * - `is_new_arrival` (boolean, opcional): ¿Es novedad?
     * - `category_id` (integer, opcional): ID de categoría
     * - `translations` (array, requerido): Array de traducciones
     * - `authors` (array, opcional): Array de IDs de autores
     * - `cover` (file, opcional): Imagen de portada
     * 
     * ```bash
     * curl -X POST "http://localhost/api/publishings/updatePublishing/1" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
     *   -F "publishing_type=1" \
     *   -F "pages=320" \
     *   -F "publish_date=2025-01-15" \
     *   -F "publish_code=978-84-9876-123-4" \
     *   -F "category_id=3" \
     *   -F "is_new_arrival=1" \
     *   -F "translations[0][language_id]=1" \
     *   -F "translations[0][title]=Laravel para Principiantes" \
     *   -F "translations[0][URL]=laravel-para-principiantes" \
     *   -F "authors[]=1" \
     *   -F "authors[]=2" \
     *   -F "cover=@/path/to/cover.jpg"
     * ```
     * 
     * @authenticated
     * 
     * @response {
     *   "ok": true,
     *   "message": "Publicación actualizada exitosamente",
     *   "data": {
     *     "id": 1,
     *     "title": "Laravel para Principiantes",
     *     "cover": "editorial_files/libros/programacion/laravel_principiantes/1234567890.jpg",
     *     "pages": 320,
     *     "publish_date": "2025-01-15",
     *     "publish_code": "978-84-9876-123-4",
     *     "category_id": 3,
     *     "publishing_type": 1,
     *     "is_new_arrival": true
     *   }
     * }
     * 
     * @response 400 {
     *   "ok": false,
     *   "error": "Bad Request",
     *   "message": "El numero de ID es requerido"
     * }
     * 
     * @response 404 {
     *   "ok": false,
     *   "error": "Not Found",
     *   "message": "Publicación no encontrada"
     * }
     * 
     * @response 422 {
     *   "ok": false,
     *   "error": "Validation Error",
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "pages": ["The pages field is required."],
     *     "publish_code": ["The publish code format is invalid."]
     *   }
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    //! Distinguir entre el idioma de la publicacion
    public function update(Request $request, $id = null)
    {

        try {

            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El numero de ID es requerido');
            }

            $updatedPublishing = Publishing::with(['authors', 'category', 'type', 'details'])->findOrFail($id);

            //! Operacion para los libros
            if ($request->has('publishing_type') && $request->input('publishing_type') == 1) {
                if (!Publishing::where('id', $id)->where('publishing_type', 1)->exists()) {
                    throw new NotFoundException('Publicación no encontrada');
                }

                $validator = Validator::make($request->all(), [
                    'publishing_type' => 'required|integer|exists:publishing_types,id',
                    // 'title' => 'required|string|max:255',
                    'pages' => 'required|integer|min:1',
                    'publish_date' => 'required|date',
                    'publish_code' => [
                        'required',
                        'string',
                        'regex:/^\d{3}-\d{2}-\d{4}-\d{3}-\d$/'
                    ],
                    'category_id' => 'integer|nullable',
                    'is_new_arrival' => 'boolean',
                    // 'URL' => 'nullable|string',
                    'translations.*.language_id' => 'required|integer|exists:languages,id',
                    'translations.*.title' => 'required|string|max:255',
                    'translations.*.URL' => 'nullable|string',
                    'authors' => 'array',
                    'authors.*' => 'integer|exists:authors,id',
                    'cover' => 'nullable',
                ]);

                if ($validator->fails()) {
                    throw new ValidationException($validator);
                }

                DB::transaction(function () use ($id, $request, &$publishing) {
                    // Buscar la publicación con algunas relaciones
                    $publishing = Publishing::with(['category', 'type', 'details', 'translations'])->findOrFail($id);

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
                        // 'title',
                        'pages',
                        'publish_date',
                        'publish_code',
                        'category_id',
                        'is_new_arrival',
                        // 'URL'
                    ]));
                    //Actualizar la traduccion asociada 
                    if ($request->has('translations')) {
                        foreach ($request->input('translations') as $translationData) {
                            $publishing->translations()->updateOrCreate(
                                ['publishing_id' => $id, 'language_id' => $translationData['language_id']],
                                [
                                    'title' => $translationData['title'],
                                    'URL' => $translationData['URL'] ? $translationData['URL'] : null,
                                ]
                            );
                        }
                    }
                    // Sincronizar autores
                    $publishing->authors()->sync($request->input('authors'));
                });

            }

            //? Operacion para las revistas
            if ($request->has('publishing_type') && $request->input('publishing_type') == 2) {

                //Esta validacion asegura que la publicación sea del tipo revista
                if (!Publishing::where('id', $id)->where('publishing_type', 2)->exists()) {
                    throw new NotFoundException('Publicación no encontrada');
                }

                $validator = Validator::make($request->all(), [
                    // 'title' => 'required|string|max:255',
                    'pages' => 'required|integer|min:1',
                    'publish_date' => 'required|date',
                    'publish_code' => [
                        'required',
                        'string',
                        'regex:/^\d{3}-\d{2}-\d{4}-\d{3}-\d$/'
                    ],
                    'age' => 'nullable|integer|min:1',
                    'age_number' => 'nullable|integer|min:1',
                    'volume' => 'nullable|integer|min:1',
                    'is_new_arrival' => 'boolean',
                    'category_id' => 'integer|nullable',
                    'publishing_type' => 'required|integer|exists:publishing_types,id',
                    'authors' => 'array',
                    'authors.*' => 'integer|exists:authors,id',
                    'cover' => 'nullable',
                ]);

                if ($validator->fails()) {
                    throw new ValidationException($validator);
                }

                // Usar transacción para asegurar integridad de datos
                DB::transaction(function () use ($id, $request, &$publishing) {
                    $publishing = Publishing::with(['category', 'type', 'details', 'translations'])->findOrFail($id);

                    if ($request->hasFile('cover')) {
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
                        // 'title',
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

                    if ($request->has('translations')) {
                        foreach ($request->input('translations') as $translationData) {
                            $publishing->translations()->updateOrCreate(
                                ['publishing_id' => $id, 'language_id' => $translationData['language_id']],
                                [
                                    'title' => $translationData['title'],
                                ]
                            );
                        }
                    }

                    // Sincronizar autores
                    $publishing->authors()->sync($request->input('authors'));
                });
            }

            return $this->successResponse($updatedPublishing, 'Publicación actualizada exitosamente', null, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new NotFoundException('Publicación no encontrada');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            // Dejar que las excepciones de API lleguen al Handler
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al actualizar la publicación', $e->getMessage(), $e);
        }
    }

    /**
     * Cambiar estado de archivo de una publicación
     * 
     * Alterna el estado de archivo de una publicación entre activo (0) y archivado (1).
     * Las publicaciones archivadas no aparecen en las búsquedas normales pero pueden ser recuperadas.
     * 
     * **Autenticación requerida:**
     * Este endpoint requiere un token JWT válido en el header de autorización.
     * 
     * **Parámetros de ruta (URL Parameters):**
     * - `id` (integer, requerido): ID de la publicación. Ejemplo: `1`
     * 
     * ```bash
     * curl -X POST "http://localhost/api/publishings/updateStatus/1" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
     * ```
     * 
     * **Nota:** Este endpoint alterna el estado automáticamente. Si está activo (0) lo archiva (1) y viceversa.
     * 
     * @authenticated
     * 
     * @response {
     *   "ok": true,
     *   "message": "Publicación archivada exitosamente",
     *   "data": null
     * }
     * 
     * @response {
     *   "ok": true,
     *   "message": "Publicación reactivada exitosamente",
     *   "data": null
     * }
     * 
     * @response 400 {
     *   "ok": false,
     *   "error": "Bad Request",
     *   "message": "El ID de publicación es requerido"
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    //Actualizando el estado de la publicacion  (campo is_archived)
    // 1 - Archivado
    // 0 - No archivado
    public function toggleStatus($id = null)
    {
        try {

            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El ID de publicación es requerido');
            }
            $publishing = Publishing::findOrFail($id);
            $publishing->is_archived = !$publishing->is_archived;
            $publishing->save();

            $message = $publishing->is_archived ? 'Publicación reactivada exitosamente' : 'Publicación archivada exitosamente';

            return $this->successResponse(null, $message, null, 200);

        } catch (Exception $e) {
            throw new ServerErrorException('Error al actualizar el estado de la publicación', null, $e);
        }
    }

    /**
     * Resumen del dashboard
     * 
     * Obtiene un resumen estadístico con el conteo total de libros y revistas en el sistema.
     * Útil para mostrar en el dashboard o página principal.
     * 
     * **Autenticación requerida:**
     * Este endpoint requiere un token JWT válido en el header de autorización.
     * 
     * ```bash
     * curl -X GET "http://localhost/api/publishings/summary" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
     * ```
     * 
     * @authenticated
     * 
     * @response {
     *   "ok": true,
     *   "message": "Resumen del dashboard obtenido correctamente",
     *   "data": {
     *     "books": 125,
     *     "magazines": 87
     *   }
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated"
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
     * Eliminar una publicación
     * 
     * Elimina permanentemente una publicación del sistema junto con todas sus relaciones (autores, detalles, traducciones).
     * También elimina la imagen de portada del servidor si existe.
     * 
     * **Autenticación requerida:**
     * Este endpoint requiere un token JWT válido en el header de autorización.
     * 
     * **Parámetros de ruta (URL Parameters):**
     * - `id` (integer, requerido): ID de la publicación a eliminar. Ejemplo: `1`
     * 
     * ```bash
     * curl -X POST "http://localhost/api/publishings/deletePublishing/1" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
     * ```
     * 
     * **IMPORTANTE:** 
     * - La publicación DEBE estar archivada (is_archived=1) antes de poder eliminarla.
     * - Esta operación es IRREVERSIBLE y eliminará toda la información relacionada.
     * - Se eliminarán: autores asociados, detalles, traducciones y archivo de portada.
     * 
     * @authenticated
     * 
     * @response {
     *   "ok": true,
     *   "message": "Publicación eliminada exitosamente",
     *   "data": null
     * }
     * 
     * @response 400 {
     *   "ok": false,
     *   "error": "Bad Request",
     *   "message": "El ID de publicación es requerido"
     * }
     * 
     * @response 400 {
     *   "ok": false,
     *   "error": "Bad Request",
     *   "message": "La publicación debe estar archivada para poder eliminarla"
     * }
     * 
     * @response 404 {
     *   "ok": false,
     *   "error": "Not Found",
     *   "message": "Publicación no encontrada"
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
                $publishing->translations()->delete(); //Borrar tambien las traducciones asociadas
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
