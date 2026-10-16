<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Http\Traits\SearchFilter;
use App\PublishingType;
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
    $this->middleware("auth:api")->except([
      "index",
      "searchByURL",
      "globalSearch",
    ]);
  }

  private function deleteCoverImage($publishing)
  {
    try {
      if ($publishing->cover) {
        $typeFolder = $publishing->publishing_type == 1 ? "libros" : "revistas";

        $categoryFolder = "sin_categoria";
        if ($publishing->relationLoaded("category") && $publishing->category) {
          $categoryFolder = $this->sanitizeFileName(
            $publishing->category->title,
          );
        }

        $relativePath = "editorial_files/{$typeFolder}/{$categoryFolder}";
        $fullPath = public_path($relativePath . "/" . $publishing->cover);

        if (file_exists($fullPath)) {
          unlink($fullPath);
        }
      }
    } catch (Exception $e) {
      \Log::warning("No se pudo eliminar la imagen: {$publishing->cover}");
    }
  }

  private function sanitizeFileName($name)
  {
    // Convertir a minúsculas
    $name = mb_strtolower($name, "UTF-8");

    // Reemplazar caracteres especiales
    $name = str_replace(
      ["á", "é", "í", "ó", "ú", "ñ", " ", "/", "\\", "&"],
      ["a", "e", "i", "o", "u", "n", "_", "_", "_", "y"],
      $name,
    );

    // Eliminar caracteres no permitidos
    $name = preg_replace("/[^a-z0-9_-]/", "", $name);

    // Limitar longitud
    return substr($name, 0, 50);
  }

  private function storeCoverImage($file, $publishingType, $categoryId, $title)
  {
    try {
      // Determinar tipo de publicación
      $typeFolder = $publishingType == 1 ? "libros" : "revistas";

      // Nombre de categoria por defecto
      $categoryFolder = "sin_categoria";

      // Obtener nombre de categoría
      if ($categoryId) {
        $category = \App\Category::find($categoryId);
        if ($category) {
          $categoryFolder = $this->sanitizeFileName($category->title);
        }
      }

      // Sanitizar nombre de la publicación
      $fileTitle = $this->sanitizeFileName($title);

      // Generar nombre único para el archivo
      $extension = $file->getClientOriginalExtension();
      $fileName = $fileTitle . "_" . time() . "_" . uniqid() . "." . $extension;

      // Ruta completa: editorial_files/libros|revistas/categoria/{imagen}.{jpg/png}
      $relativePath = "editorial_files/{$typeFolder}/{$categoryFolder}";
      $fullPath = public_path($relativePath);

      // Crear directorio si no existe
      if (!file_exists($fullPath)) {
        mkdir($fullPath, 0755, true);
      }

      // Mover archivo
      $file->move($fullPath, $fileName);

      // Retornar nombre de archivo para guardar en la BD
      // La ruta relativa sera manejada por el frontend (el servidor de archivos)
      return $fileName;
      // return "{$relativePath}/{$fileName}";
    } catch (Exception $e) {
      throw new ServerErrorException("Error al almacenar la imagen", null, $e);
    }
  }

  /**
   * Listar publicaciones
   *
   * Obtiene una lista paginada de publicaciones (libros y revistas) con opciones de filtrado, búsqueda y ordenamiento.
   *
   * **Autenticación requerida:**
   * Este endpoint NO requiere autenticación (público).
   *
   * **Parámetros de consulta (Query Parameters):**
   * - `page` (integer, opcional): Número de página. Por defecto: 1. Ejemplo: `1`
   * - `limit` (integer, opcional): Resultados por página (mínimo 1). Por defecto: 10. Ejemplo: `10`
   * - `publishing_type` (string, opcional): Nombre del tipo de publicación (Libro, Revista). Ejemplo: `Libro`
   * - `language_id` (integer, opcional): ID del idioma para las traducciones. Por defecto: 1. Ejemplo: `1`
   * - `search` (string, opcional): Búsqueda por título. Ejemplo: `Laravel`
   * - `category` (integer, opcional): Filtrar por ID de categoría. Ejemplo: `3`
   * - `is_archived` (integer, opcional): Filtrar por estado (0=Activo, 1=Archivado). Ejemplo: `0`
   * - `sort` (string, opcional): Campo para ordenar (id, title, created_at, updated_at). Ejemplo: `title:asc`
   *
   * ```bash
   * curl -X GET "http://localhost/api/publishings/getPublishings?page=1&limit=10&publishing_type=Libro&search=Laravel" \
   *   -H "Accept: application/json"
   * ```
   *
   * @response {
   *   "ok": true,
   *   "message": "Publicaciones obtenidas correctamente",
   *   "data": [
   *     {
   *       "idPublishing": 1,
   *       "title": "Laravel para Principiantes",
   *       "cover": "editorial_files/libros/programacion/laravel_principiantes/1234567890.jpg",
   *       "pages": 320,
   *       "publish_date": "2025-01-15",
   *       "publish_code": "978-84-9876-123-4",
   *       "category_id": 3,
   *       "publishing_type": 1,
   *       "is_new_arrival": 1,
   *       "state": 1,
   *       "created_at": "2025-11-15 10:30:00",
   *       "updated_at": "2025-11-15 10:30:00",
   *       "type": {
   *         "idPublishingType": 1,
   *         "name": "Libro"
   *       },
   *       "authors": [
   *         {
   *           "idAuthor": 1,
   *           "name": "Juan",
   *           "lastname": "García"
   *         }
   *       ],
   *       "details": [],
   *       "translation": {
   *         "idPublishingTranslation": 1,
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
        "page" => "integer|min:1",
        "limit" => "integer|min:1",
        "publishing_type" => "string|exists:publishing_types,name",
        "language_id" => "integer|exists:languages,idLanguage",
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $allowedSortFields = [
        "idPublishing",
        "title",
        "created_at",
        "updated_at",
      ];

      $limit = $request->query("limit", 10);
      $publishingTypeName = $request->query("publishing_type", null);
      $languageId = $request->query("language_id", 1);
      $search = $request->query("search", null);
      $category = $request->query("category", null);
      $isArchived = $request->has("state")
        ? (int) $request->query("state")
        : null;

      $query = Publishing::with([
        "type",
        "authors",
        "details",
        "category",
        "translation" => function ($q) use ($languageId) {
          $q->where("language_id", $languageId);
        },
      ]);

      if ($search) {
        $query->where(function ($q) use ($search) {
          $q->where("title", "like", "%" . $search . "%")->orWhere(
            "publish_code",
            "like",
            "%" . $search . "%",
          );
        });
      }

      if ($isArchived !== null) {
        $query->where("state", $isArchived);
      }

      if ($category) {
        $query->where("category_id", $category);
      }

      //Obtener el ID del tipo de publicación
      if ($publishingTypeName != null && $publishingTypeName != "") {
        $publishing_type = PublishingType::where(
          "name",
          $publishingTypeName,
        )->firstOrFail()->idPublishingType;
        $query->where("publishing_type", $publishing_type);
      }

      if ($request->has("sort")) {
        $this->search($request, $query, $allowedSortFields);
      } else {
        $query->orderBy("idPublishing", "desc");
      }

      $publishings = $query->paginate($limit);

      $pagination = [
        "total" => $publishings->total(),
        "current_page" => $publishings->currentPage(),
        "last_page" => $publishings->lastPage(),
        "per_page" => $publishings->perPage(),
        "from" => $publishings->firstItem(),
        "to" => $publishings->lastItem(),
      ];

      return $this->successResponse(
        $publishings->items(),
        "Publicaciones obtenidas correctamente",
        $pagination,
        200,
      );
    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException(
        "Error al obtener las publicaciones",
        null,
        $e,
      );
    }
  }

  /**
   * Obtener una publicación por ID
   *
   * Obtiene la información detallada de una publicación específica con todas sus relaciones (tipo, autores, detalles, traducciones).
   * Esta función es para uso interno del CMS usando ID numérico.
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
   *     "idPublishing": 1,
   *     "title": "Laravel para Principiantes",
   *     "cover": "editorial_files/libros/programacion/laravel_principiantes/1234567890.jpg",
   *     "pages": 320,
   *     "publish_date": "2025-01-15",
   *     "publish_code": "978-84-9876-123-4",
   *     "category_id": 3,
   *     "publishing_type": 1,
   *     "is_new_arrival": 1,
   *     "state": 1,
   *     "type": {
   *       "idPublishingType": 1,
   *       "name": "Libro"
   *     },
   *     "authors": [
   *       {
   *         "idAuthor": 1,
   *         "name": "Juan",
   *         "lastname": "García"
   *       }
   *     ],
   *     "translations": [
   *       {
   *         "idPublishingTranslation": 1,
   *         "title": "Laravel para Principiantes",
   *         "URL": "laravel-para-principiantes",
   *         "language": {
   *           "idLanguage": 1,
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
      if (empty($id)) {
        throw new BadRequestException(
          "El ID de la publicación es requerido y debe ser un número entero",
        );
      }

      if (!Publishing::where("idPublishing", $id)->exists()) {
        throw new NotFoundException("Publicación no encontrada");
      }

      $publishing = Publishing::with([
        "type",
        "category",
        "authors",
        "details",
        "translations.language",
      ])
        ->where("idPublishing", $id)
        ->firstOrFail();
      return $this->successResponse(
        $publishing,
        "Publicación obtenida exitosamente",
      );
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      throw new NotFoundException("Publicación no encontrada");
    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las excepciones de API lleguen al Handler
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException(
        "Error al obtener la publicación",
        null,
        $e,
      );
    }
  }

  /**
   * Buscar una publicación por URL/slug
   *
   * Obtiene la información detallada de una publicación específica usando su URL/slug con todas sus relaciones.
   * Esta función es para uso público del frontend usando URLs amigables.
   * Solo retorna publicaciones activas (is_archived=0).
   *
   * **Autenticación requerida:**
   * Este endpoint NO requiere autenticación (público).
   *
   * **Parámetros de ruta (URL Parameters):**
   * - `publishingURL` (string, requerido): URL/slug de la publicación a consultar. Ejemplo: `laravel-para-principiantes`
   *
   * ```bash
   * curl -X GET "http://localhost/api/publishings/searchByURL/laravel-para-principiantes" \
   *   -H "Accept: application/json"
   * ```
   *
   * @response {
   *   "ok": true,
   *   "message": "Publicación encontrada exitosamente",
   *   "data": {
   *     "idPublishing": 1,
   *     "title": "Laravel para Principiantes",
   *     "URL": "laravel-para-principiantes",
   *     "cover": "editorial_files/libros/programacion/laravel_principiantes/1234567890.jpg",
   *     "pages": 320,
   *     "publish_date": "2025-01-15",
   *     "publish_code": "978-84-9876-123-4",
   *     "category_id": 3,
   *     "publishing_type": 1,
   *     "is_new_arrival": 1,
   *     "state": 1,
   *     "type": {
   *       "idPublishingType": 1,
   *       "name": "Libro"
   *     },
   *     "authors": [
   *       {
   *         "idAuthor": 1,
   *         "name": "Juan",
   *         "lastname": "García"
   *       }
   *     ],
   *     "details": [],
   *     "translations": [
   *       {
   *         "idPublishingTranslation": 1,
   *         "title": "Laravel para Principiantes",
   *         "URL": "laravel-para-principiantes",
   *         "language": {
   *           "idLanguage": 1,
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
   *   "message": "La URL de la publicación es requerida"
   * }
   *
   * @response 404 {
   *   "ok": false,
   *   "error": "Not Found",
   *   "message": "Publicación no encontrada"
   * }
   */
  public function searchByURL($publishingURL = null)
  {
    try {
      if (empty($publishingURL)) {
        throw new BadRequestException("La URL de la publicación es requerida");
      }

      // Buscar por URL principal de la publicación
      $publishing = Publishing::with([
        "type",
        "authors",
        "details",
        "category",
        "translations.language",
      ])
        ->where("URL", $publishingURL)
        ->where("state", 1)
        ->first();

      if (!$publishing) {
        throw new NotFoundException("Publicación no encontrada");
      }

      return $this->successResponse(
        $publishing,
        "Publicación encontrada exitosamente",
      );
    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las excepciones de API lleguen al Handler
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException(
        "Error al buscar la publicación",
        null,
        $e,
      );
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
   * **Parámetros del Body (JSON):**
   * - `title` (string, requerido): Título principal de la publicación (máximo 255 caracteres, único). Ejemplo: `Laravel para Principiantes`
   * - `URL` (string, opcional): URL slug de la publicación. Ejemplo: `laravel-para-principiantes`
   * - `category_id` (integer, opcional): ID de la categoría. Ejemplo: `3`
   * - `publishing_type` (string, requerido): Nombre del tipo de publicación (Libro, Revista). Ejemplo: `Libro`
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
   *   "publishing_type": "Libro",
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
   *   -d '{"title":"Laravel para Principiantes","publishing_type":"Libro","translations":[{"language_id":1,"title":"Laravel para Principiantes"}]}'
   * ```
   *
   * @authenticated
   *
   * @response {
   *   "ok": true,
   *   "message": "Publicacion creada exitosamente",
   *   "data": {
   *     "idPublishing": 1,
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
        "title" => "required|string|max:255|unique:publishings,title",
        "URL" => "nullable|string",
        "category_id" => "nullable|integer|exists:categories,idCategory",
        "publishing_type" => "string|exists:publishing_types,name",
        // Traducciones
        "translations" => "required|array|min:1",
        "translations.*.language_id" =>
          "required|integer|exists:languages,idLanguage",
        "translations.*.title" => "required|string|max:255",
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $publishing_type = PublishingType::where(
        "name",
        $request->input("publishing_type"),
      )->firstOrFail()->idPublishingType;

      DB::transaction(function () use (
        $request,
        &$publishing,
        $publishing_type,
      ) {
        $publishing = Publishing::create([
          "title" => $request->input("title"),
          "URL" => $request->input("URL"),
          "category_id" => $request->input("category_id"),
          "cover" => null,
          "age" => null,
          "volume" => null,
          "volume_date" => null,
          "publish_code" => null,
          "is_new_arrival" => 0,
          "publish_date" => null,
          "publisher" => null,
          "stock" => null,
          "author_id" => null,
          "publishing_type" => $publishing_type,
          "age_number" => null,
          "pages" => null,
        ]);
        foreach ($request->input("translations") as $translation) {
          $publishing->translations()->create([
            "language_id" => $translation["language_id"],
            "title" => $translation["title"],
          ]);
        }
      });
      return $this->successResponse(
        $publishing,
        "Publicacion creada exitosamente",
        null,
        200,
      );
    } catch (\Illuminate\Validation\ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las excepciones de API lleguen al Handler
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException("Error al crear la publicación", null, $e);
    }
  }

  /**
   * Actualizar una publicación
   *
   * Actualiza una publicación existente. Los campos requeridos varían según el tipo de publicación:
   * - **Libros (publishing_type=1)**: Requiere páginas, fecha de publicación, código ISBN
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
   * - `title` (string, opcional): Título de la publicación (único excepto para el registro actual)
   * - `pages` (integer, requerido): Número de páginas (mínimo 1). Ejemplo: `320`
   * - `publish_date` (date, requerido): Fecha de publicación. Ejemplo: `2025-01-15`
   * - `publish_code` (string, requerido): Código ISBN (formato: XXX-XX-XXXX-XXX-X). Ejemplo: `978-84-9876-123-4`
   * - `category_id` (integer, opcional): ID de categoría. Ejemplo: `3`
   * - `is_new_arrival` (boolean, opcional): ¿Es novedad? Ejemplo: `true`
   * - `URL` (string, opcional): URL slug de la publicación
   * - `resource_URL` (string, opcional): URL del recurso/documento
   * - `translations` (array, requerido): Array de traducciones
   * - `translations[].language_id` (integer, requerido): ID del idioma
   * - `translations[].title` (string, requerido): Título traducido (máximo 255 caracteres)
   * - `authors` (array, opcional): Array de IDs de autores. Ejemplo: `[1, 2]`
   * - `cover` (file, opcional): Imagen de portada (JPEG, PNG)
   *
   * **Parámetros del Body (para Revistas - publishing_type=2):**
   * - `publishing_type` (integer, requerido): Debe ser 2 para revistas
   * - `title` (string, opcional): Título de la publicación (único excepto para el registro actual)
   * - `pages` (integer, requerido): Número de páginas (mínimo 1)
   * - `publish_date` (date, requerido): Fecha de publicación
   * - `publish_code` (string, requerido): Código ISSN (formato: XXX-XX-XXXX-XXX-X)
   * - `age` (integer, opcional): Año de la revista (mínimo 1). Ejemplo: `2025`
   * - `age_number` (integer, opcional): Número dentro del año (mínimo 1). Ejemplo: `3`
   * - `volume` (integer, opcional): Volumen (mínimo 1). Ejemplo: `12`
   * - `URL` (string, opcional): URL slug de la publicación
   * - `is_new_arrival` (boolean, opcional): ¿Es novedad?
   * - `category_id` (integer, opcional): ID de categoría
   * - `translations` (array, requerido): Array de traducciones
   * - `translations[].language_id` (integer, requerido): ID del idioma
   * - `translations[].title` (string, requerido): Título traducido (máximo 255 caracteres)
   * - `authors` (array, opcional): Array de IDs de autores
   * - `cover` (file, opcional): Imagen de portada
   *
   * **Nota:** Si se actualiza la traducción en español (language_id=1), el título principal de la publicación también se actualiza automáticamente.
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
   *     "idPublishing": 1,
   *     "title": "Laravel para Principiantes",
   *     "cover": "editorial_files/libros/programacion/laravel_principiantes/1234567890.jpg",
   *     "pages": 320,
   *     "publish_date": "2025-01-15",
   *     "publish_code": "978-84-9876-123-4",
   *     "category_id": 3,
   *     "publishing_type": 1,
   *     "is_new_arrival": 1,
   *     "authors": [],
   *     "category": null,
   *     "type": {
   *       "idPublishingType": 1,
   *       "name": "Libro"
   *     },
   *     "details": []
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
  public function update(Request $request, $id = null)
  {
    try {
      if (empty($id) || !is_numeric($id)) {
        throw new BadRequestException("El numero de ID es requerido");
      }

      $updatedPublishing = Publishing::with([
        "authors",
        "category",
        "type",
        "details",
      ])->findOrFail($id);
      $publishingTypeName = PublishingType::where(
        "idPublishingType",
        $request->input("publishing_type"),
      )->firstOrFail()->name;

      //! Operacion para los libros
      if ($publishingTypeName == "Libro") {
        if (
          !Publishing::where("idPublishing", $id)
            ->where("publishing_type", $request->input("publishing_type"))
            ->exists()
        ) {
          throw new NotFoundException("Publicación no encontrada");
        }
        $validator = Validator::make($request->all(), [
          "title" => "string|unique:publishings,title," . $id . ",idPublishing",
          "pages" => "required|integer|min:1",
          "publish_date" => "required|date",
          "publish_code" => [
            "required",
            "string",
            // 978-99923-50-36-2
            'regex:/^\d{3}-\d{5}-\d{2}-\d{2}-\d$/',
          ],
          "category_id" => "integer|nullable",
          "is_new_arrival" => "boolean",
          "URL" => "nullable|string",
          "resource_URL" => "nullable|string",
          "translations.*.language_id" =>
            "required|integer|exists:languages,idLanguage",
          "translations.*.title" => "required|string|max:255",
          "authors" => "array",
          "authors.*" => "integer|exists:authors,idAuthor",
          "cover" => "nullable",
        ]);

        if ($validator->fails()) {
          throw new ValidationException($validator);
        }

        DB::transaction(function () use ($id, $request, &$publishing) {
          // Buscar la publicación con algunas relaciones
          $publishing = Publishing::with([
            "category",
            "type",
            "details",
            "translations",
          ])->findOrFail($id);

          // Actualizar atributos principales (sin URL, se actualiza solo si hay traducción en español)
          $updateData = $request->only([
            "resource_URL",
            "pages",
            "publish_date",
            "publish_code",
            "category_id",
            "is_new_arrival",
          ]);

          if ($request->hasFile("cover")) {
            if ($publishing->cover) {
              $this->deleteCoverImage($publishing);
            }

            $coverName = $this->storeCoverImage(
              $request->file("cover"),
              $request->input("publishing_type"),
              $request->input("category_id"),
              $request->input("translations")[0]["title"]
                ? $request->input("translations")[0]["title"]
                : "",
            );

            $updateData["cover"] = $coverName;
          }

          // Procesar traducciones: actualizar título/URL principal si es español y sincronizar todas las traducciones
          if ($request->has("translations")) {
            foreach ($request->input("translations") as $translationData) {
              // Si la traducción es en español (id: 1), actualizar el título principal y URL
              if ($translationData["language_id"] == 1) {
                $updateData["title"] = $translationData["title"];
                if ($request->has("URL")) {
                  $updateData["URL"] = $request->input("URL");
                }
              }

              // Actualizar o crear la traducción
              $publishing->translations()->updateOrCreate(
                [
                  "publishing_id" => $id,
                  "language_id" => $translationData["language_id"],
                ],
                ["title" => $translationData["title"]],
              );
            }
          }

          $publishing->update($updateData);

          // Sincronizar autores
          $publishing->authors()->sync($request->input("authors"));
        });
      }

      //? Operacion para las revistas
      if ($publishingTypeName == "Revista") {
        if (
          !Publishing::where("idPublishing", $id)
            ->where("publishing_type", $request->input("publishing_type"))
            ->exists()
        ) {
          throw new NotFoundException("Publicación no encontrada");
        }

        $validator = Validator::make($request->all(), [
          "title" => "string|unique:publishings,title," . $id . ",idPublishing",
          "pages" => "required|integer|min:1",
          "publish_date" => "required|date",
          "publish_code" => ["required", "string", 'regex:/^\d{4}-\d{4}$/'],
          "age" => "nullable|integer|min:1",
          "URL" => "nullable|string",
          "age_number" => "nullable|integer|min:1",
          "volume" => "nullable|integer|min:1",
          "is_new_arrival" => "boolean",
          "category_id" => "integer|nullable",
          "translations.*.language_id" =>
            "required|integer|exists:languages,idLanguage",
          "translations.*.title" => "required|string|max:255",
          "authors" => "array",
          "authors.*" => "integer|exists:authors,idAuthor",
          "cover" => "nullable",
        ]);

        if ($validator->fails()) {
          throw new ValidationException($validator);
        }

        // Usar transacción para asegurar integridad de datos
        DB::transaction(function () use ($id, $request, &$publishing) {
          $publishing = Publishing::with([
            "category",
            "type",
            "details",
            "translations",
          ])->findOrFail($id);

          // Actualizar atributos principales (sin URL, se actualiza solo si hay traducción en español)
          $updateData = $request->only([
            "publish_date",
            "publish_code",
            "age",
            "age_number",
            "pages",
            "volume",
            "is_new_arrival",
            "category_id",
          ]);

          if ($request->hasFile("cover")) {
            if ($publishing->cover) {
              $this->deleteCoverImage($publishing);
            }

            $coverName = $this->storeCoverImage(
              $request->file("cover"),
              $request->input("publishing_type"),
              $request->input("category_id"),
              $request->input("title"),
            );

            $updateData["cover"] = $coverName;
          }

          // Procesar traducciones: actualizar título/URL principal si es español y sincronizar todas las traducciones
          if ($request->has("translations")) {
            foreach ($request->input("translations") as $translationData) {
              // Si la traducción es en español (id: 1), actualizar el título principal y URL
              if ($translationData["language_id"] == 1) {
                $updateData["title"] = $translationData["title"];
                if ($request->has("URL")) {
                  $updateData["URL"] = $request->input("URL");
                }
              }

              // Actualizar o crear la traducción
              $publishing->translations()->updateOrCreate(
                [
                  "publishing_id" => $id,
                  "language_id" => $translationData["language_id"],
                ],
                ["title" => $translationData["title"]],
              );
            }
          }

          $publishing->update($updateData);

          // Sincronizar autores
          $publishing->authors()->sync($request->input("authors"));
        });
      }

      return $this->successResponse(
        $publishing,
        "Publicación actualizada exitosamente",
        null,
        200,
      );
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      throw new NotFoundException("Publicación no encontrada");
    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las excepciones de API lleguen al Handler
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException(
        "Error al actualizar la publicacion",
        null,
        $e,
      );
    }
  }

  /**
   * Cambiar estado de archivo de una publicación
   *
   * Alterna el estado de archivo de una publicación entre activo (0) y archivado (1).
   * Las publicaciones archivadas no aparecen en las búsquedas públicas pero pueden ser recuperadas.
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
   * **Nota:** Este endpoint alterna el estado automáticamente:
   * - Si `is_archived=0` (activo) → cambia a `is_archived=1` (archivado) → mensaje: "Publicación reactivada exitosamente"
   * - Si `is_archived=1` (archivado) → cambia a `is_archived=0` (activo) → mensaje: "Publicación archivada exitosamente"
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
  //Actualizando el estado de la publicacion  (campo state)
  // 1 - Activo
  // 0 - Inactivo
  public function toggleStatus($id = null)
  {
    try {
      if (empty($id) || !is_numeric($id)) {
        throw new BadRequestException("El ID de publicación es requerido");
      }
      $publishing = Publishing::findOrFail($id);
      $publishing->state = !$publishing->state;
      $publishing->save();

      $message = $publishing->state
        ? "Publicación archivada exitosamente"
        : "Publicación reactivada exitosamente";

      return $this->successResponse(null, $message, null, 200);
    } catch (Exception $e) {
      throw new ServerErrorException(
        "Error al actualizar el estado de la publicación",
        null,
        $e,
      );
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
      $publishing_type = PublishingType::whereIn("name", [
        "Libro",
        "Revista",
      ])->pluck("idPublishingType", "name");

      $booksCount = Publishing::where(
        "publishing_type",
        $publishing_type["Libro"],
      )->count();
      $magazinesCount = Publishing::where(
        "publishing_type",
        $publishing_type["Revista"],
      )->count();

      $data = [
        "books" => $booksCount,
        "magazines" => $magazinesCount,
      ];

      return $this->successResponse(
        $data,
        "Resumen del dashboard obtenido correctamente",
        null,
        200,
      );
    } catch (Exception $e) {
      throw new ServerErrorException(
        "Error al obtener el resumen del dashboard",
        null,
        $e,
      );
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
        throw new BadRequestException("El ID de publicación es requerido");
      }

      $publishing = Publishing::findOrFail($id);

      //Si el campo state es 1, no se puede eliminar
      if ($publishing->state == 1) {
        throw new BadRequestException(
          "La publicación debe estar archivada para poder eliminarla",
        );
      }

      DB::transaction(function () use ($publishing) {
        if ($publishing->cover) {
          $this->deleteCoverImage($publishing);
        }

        $publishing->authors()->detach();
        $publishing->details()->delete();
        $publishing->translations()->delete(); //Borrar tambien las traducciones asociadas
        $publishing->delete();
      });

      return $this->successResponse(
        null,
        "Publicación eliminada exitosamente",
        null,
        200,
      );
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      throw new NotFoundException("Publicación no encontrada");
    } catch (\App\Exceptions\ApiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException(
        "Error al eliminar la publicación",
        null,
        $e,
      );
    }
  }

  /**
   * Búsqueda global
   *
   * Realiza una búsqueda global de libros por título, código ISBN o nombre/apellido del autor.
   * Solo retorna libros activos (state=1).
   *
   * **Autenticación requerida:**
   * Este endpoint NO requiere autenticación (público).
   *
   * **Parámetros de consulta (Query Parameters):**
   * - `search` (string, requerido): Término de búsqueda por título, código ISBN o nombre del autor. Ejemplo: `Laravel`
   *
   * ```bash
   * curl -X GET "http://localhost/api/publishings/globalSearch?search=Laravel" \
   *   -H "Accept: application/json"
   * ```
   *
   * @response {
   *   "ok": true,
   *   "message": "Búsqueda global realizada exitosamente",
   *   "data": {
   *     "libros": [
   *       {
   *         "idPublishing": 1,
   *         "title": "Laravel para Principiantes",
   *         "URL": "laravel_para_principiantes",
   *         "resource_URL": "https://example.com/laravel.pdf"
   *       }
   *     ]
   *   }
   * }
   *
   * @response 400 {
   *   "ok": false,
   *   "error": "Bad Request",
   *   "message": "El parámetro de búsqueda es requerido"
   * }
   */
  public function globalSearch(Request $request)
  {
    try {
      $query = $request->query("search", "");

      if (empty($query)) {
        throw new BadRequestException("El parámetro de búsqueda es requerido");
      }

      $libroTypeId = PublishingType::where("name", "Libro")->value(
        "idPublishingType",
      );

      // Buscar libros (publishing_type = 1) por título, ISBN o nombre del autor
      $libros = Publishing::where("publishing_type", $libroTypeId)
        ->where(function ($q) use ($query) {
          $q->where("title", "like", "%" . $query . "%")
            ->orWhere("publish_code", "like", "%" . $query . "%")
            ->orWhereHas("authors", function ($a) use ($query) {
              $a->where("name", "like", "%" . $query . "%")->orWhere(
                "lastname",
                "like",
                "%" . $query . "%",
              );
            });
        })
        ->where("state", 1)
        ->select("idPublishing", "title", "URL", "resource_URL")
        ->get();

      $data = [
        "libros" => $libros,
      ];

      return $this->successResponse(
        $data,
        "Búsqueda global realizada exitosamente",
      );
    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException(
        "Error al buscar las publicaciones",
        null,
        $e,
      );
    }
  }
}
