<?php
namespace App\Http\Controllers;

use App\Category;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Exceptions\ConflictException;
use App\Exceptions\BadRequestException;
use App\Exceptions\ServerErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;

/**
 * @resource Categories
 *
 * Endpoints para la gestión de categorías y subcategorías del sistema editorial.
 * Usa JWT en el header: `Authorization: Bearer {token}`.
 */
class CategoryController extends Controller
{

  use ApiResponse;

  /**
   * Constructor (middleware)
   *
   * Aplica el middleware `auth:api` a todas las rutas.
   */
  public function __construct()
  {
    $this->middleware('auth:api')->except(['index', 'searchByURL']);
  }

  /**
   * Listar categorías
   * 
   * Obtiene una lista de categorías. Por defecto obtiene solo las categorías padre con sus hijas recursivamente.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros de consulta (Query Parameters):**
   * - `getAll` (boolean, opcional): Si es `true`, obtiene todas las categorías sin estructura jerárquica. 
   *   Si es `false` u omitido, obtiene solo categorías padre con sus subcategorías anidadas. 
   *   Ejemplo: `1`, `true`, `false`
   * 
   * ```bash
   * # Obtener solo categorías padre con hijas
   * curl -X GET "http://localhost/api/categories/getCategories" \
   *   -H "Accept: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
   * 
   * # Obtener todas las categorías planas
   * curl -X GET "http://localhost/api/categories/getCategories?getAll=true" \
   *   -H "Accept: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
   * ```
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Categorías obtenidas correctamente",
   *   "data": [
   *     {
   *       "id": 1,
   *       "title": "Ciencias",
   *       "description": "Categoría de ciencias",
   *       "category_type": 1,
   *       "URL": "ciencias",
   *       "state": 1,
   *       "children_recursive": [
   *         {
   *           "id": 2,
   *           "title": "Física",
   *           "description": "Subcategoría de física",
   *           "category_type": 1,
   *           "state": 1,
   *           "main_category": 1,
   *           "children_recursive": []
   *         },
   *         {
   *           "id": 3,
   *           "title": "Química",
   *           "description": "Subcategoría de química",
   *           "category_type": 1,
   *           "state": 1,
   *           "main_category": 1,
   *           "children_recursive": []
   *         }
   *       ]
   *     }
   *   ]
   * }
   * 
   * @response 401 {
   *   "message": "Unauthenticated"
   * }
   */
  public function index(Request $request)
  {
    try {

      $query = Category::getModel();

      //Obteniendo las categorías padres con las hijas
      // Cuando getAll es true, obtener todas las categorías planas (sin jerarquía).
      $categories = in_array($request->query('getAll'), ['1', 'true', true], true)
        ? $query->get(['id', 'title', 'description', 'category_type', 'state', 'URL'])
        : $query->with('childrenRecursive')
          ->whereNull('main_category')
          ->get(['id', 'title', 'description', 'category_type', 'state', 'URL']);

      return $this->successResponse($categories, 'Categorías obtenidas correctamente');

    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las excepciones de API lleguen al Handler
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException('Error al obtener las categorías', null, $e);
    }
  }

  /**
   * Obtener una categoría
   * 
   * Obtiene la información detallada de una categoría específica por su ID, incluyendo sus detalles adicionales.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros de ruta (URL Parameters):**
   * - `id` (integer, requerido): ID de la categoría a consultar. Ejemplo: `1`
   * 
   * ```bash
   * curl -X GET "http://localhost/api/categories/getCategory/1" \
   *   -H "Accept: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
   * ```
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Categoría obtenida correctamente",
   *   "data": {
   *     "id": 1,
   *     "title": "Ciencias",
   *     "description": "Categoría de ciencias",
   *     "main_category": null,
   *     "state": 1,
   *     "URL": "ciencias",
   *     "is_featured": 0,
   *     "language_id": 1,
   *     "category_type": 1,
   *     "created_at": "2025-11-15 10:30:00",
   *     "updated_at": "2025-11-15 10:30:00",
   *     "detail": {
   *       "id": 1,
   *       "category_id": 1,
   *       "language_id": 1,
   *       "about": "Información sobre la categoría",
   *       "editorial_board": "Consejo editorial",
   *       "policy": "Políticas de publicación",
   *       "norms": "Normas de la categoría",
   *       "process": "Proceso de publicación",
   *       "contact": "info@example.com"
   *     }
   *   }
   * }
   * 
   * @response 404 {
   *   "ok": false,
   *   "error": "Not Found",
   *   "message": "Categoría no encontrada"
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

      $category = Category::with(['detail'])->findOrFail($id);

      return $this->successResponse($category, 'Categoría obtenida correctamente');
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      throw new \App\Exceptions\NotFoundException('Categoría no encontrada');
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las excepciones de API lleguen al Handler
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException('Error al obtener la categoría', null, $e);
    }
  }

  /**
   * Buscar una categoria por URL/slug
   * 
   * Obtiene la información detallada de una categoria específica usando su URL/slug con todas sus relaciones.
   * Esta función es para uso público del frontend usando URLs amigables.
   * 
   * **Autenticación requerida:**
   * Este endpoint NO requiere autenticación (público).
   * 
   * **Parámetros de consulta (Query Parameters):**
   * - `categoryURL` (string, requerido): URL/slug de la categoria a consultar. Ejemplo: `ciencias`
   * 
   * ```bash
   * curl -X GET "http://localhost/api/categories/searchByURL/ciencias" \
   *   -H "Accept: application/json"
   * ```
   * 
   * @response {
   *   "ok": true,
   *   "message": "Categoría encontrada exitosamente",
   *   "data": {
   *     "id": 1,
   *     "title": "Ciencias",
   *     "description": "Categoría de ciencias",
   *     "main_category": null,
   *     "state": 1,
   *     "URL": "ciencias",
   *     "is_featured": 0,
   *     "language_id": 1,
   *     "category_type": 1,
   *     "created_at": "2025-11-15 10:30:00",
   *     "updated_at": "2025-11-15 10:30:00",
   *     "detail": {
   *       "id": 1,
   *       "category_id": 1,
   *       "language_id": 1,
   *       "about": "Información sobre la categoría",
   *       "editorial_board": "Consejo editorial",
   *       "policy": "Políticas de publicación",
   *       "norms": "Normas de la categoría",
   *       "process": "Proceso de publicación",
   *       "contact": "info@example.com"
   *     }
   *   }
   * }
   * 
   * @response 400 {
   *   "ok": false,
   *   "error": "Bad Request",
   *   "message": "La URL de la categoría es requerida"
   * }
   * 
   * @response 404 {
   *   "ok": false,
   *   "error": "Not Found",
   *   "message": "Categoría no encontrada"
   * }
   */
  public function searchByURL($categoryURL = null)
  {
    try {
      if (empty($categoryURL)) {
        throw new BadRequestException('La URL de la categoría es requerida');
      }

      // Buscar por URL principal de la publicación
      $category = Category::with(['detail'])
        ->where('URL', $categoryURL)
        ->where('state', 1) // Solo publicaciones activas para frontend
        ->first();

      if (!$category) {
        throw new \App\Exceptions\NotFoundException('Categoría no encontrada');
      }

      return $this->successResponse($category, 'Categoría encontrada exitosamente');
    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las excepciones de API lleguen al Handler
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException('Error al buscar la publicación', null, $e);
    }
  }

  /**
   * Crear una categoría
   * 
   * Crea una nueva categoría en el sistema. Por defecto se crea como categoría padre (sin categoría principal).
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros del Body:**
   * - `title` (string, requerido): Título de la categoría (máximo 255 caracteres). Ejemplo: `Ciencias`
   * - `description` (string, opcional): Descripción de la categoría. Ejemplo: `Categoría de ciencias`
   * - `category_type` (integer, opcional): Tipo de categoría. Ejemplo: `1`
   * - `language_id` (integer, opcional): ID del idioma. Por defecto: `1`. Ejemplo: `1`
   * 
   * ```json
   * {
   *   "title": "Ciencias",
   *   "description": "Categoría de ciencias exactas",
   *   "category_type": 1,
   *   "language_id": 1,
   *   "URL": "ciencias"
   * }
   * ```
   * 
   * ```bash
   * curl -X POST "http://localhost/api/categories/createCategory" \
   *   -H "Accept: application/json" \
   *   -H "Content-Type: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
   *   -d '{"title":"Ciencias","description":"Categoría de ciencias exactas","category_type":1}'
   * ```
   * 
   * **Nota:** La categoría se crea activa (`state=1`) y como categoría padre (`main_category=null`) por defecto.
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Categoría creada correctamente",
   *   "data": {
   *     "id": 1,
   *     "title": "Ciencias",
   *     "description": "Categoría de ciencias exactas",
   *     "main_category": null,
   *     "URL": "ciencias",
   *     "state": 1,
   *     "is_featured": 0,
   *     "language_id": 1,
   *     "category_type": 1,
   *     "created_at": "2025-11-15 10:30:00",
   *     "updated_at": "2025-11-15 10:30:00"
   *   }
   * }
   * 
   * @response 409 {
   *   "ok": false,
   *   "error": "Conflict",
   *   "message": "La categoría ya existe"
   * }
   * 
   * @response 422 {
   *   "ok": false,
   *   "error": "Validation Error",
   *   "message": "Los datos proporcionados no son válidos",
   *   "errors": {
   *     "title": ["The title field is required."]
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

      //!TODO: AGREGAR LANGUAGE ID DESPUES
      $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255|unique:categories,title',
        'description' => 'string',
        'category_type' => 'integer',
        'URL' => 'string|unique:categories,URL'
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $category = Category::create([
        'title' => $request->input('title'),
        'description' => $request->input('description'),
        'main_category' => null, // Por defecto, se crea como categoría padre
        'state' => 1, //Se crea como categoria activa
        'is_featured' => 0,
        'language_id' => $request->input('language_id', 1), // Por defecto, idioma 1
        'category_type' => $request->input('category_type'),
        'URL' => $request->input('URL')
      ]);

      return $this->successResponse($category, 'Categoría creada correctamente');

    } catch (\Illuminate\Validation\ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las excepciones de API lleguen al Handler
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException('Error al crear la categoría', null, $e);
    }

  }

  /**
   * Actualizar una categoría
   * 
   * Actualiza la información de una categoría existente, incluyendo sus detalles adicionales.
   * Permite convertir una categoría padre en subcategoría o viceversa.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros de ruta (URL Parameters):**
   * - `id` (integer, requerido): ID de la categoría a actualizar. Ejemplo: `1`
   * 
   * **Parámetros del Body:**
   * - `title` (string, requerido): Nuevo título de la categoría (máximo 255 caracteres). Ejemplo: `Ciencias Exactas`
   * - `description` (string, opcional): Nueva descripción. Ejemplo: `Categoría actualizada`
   * - `URL` (string, opcional): Nueva URL/slug de la categoría. Ejemplo: `ciencias_exactas`
   * - `main_category` (integer, nullable): ID de la categoría padre. Si es `null` o vacío, se mantiene como categoría padre. Ejemplo: `2`
   * - `is_featured` (boolean, opcional): Si la categoría es destacada. Ejemplo: `true`
   * - `about` (string, opcional): Información sobre la categoría. Ejemplo: `Sobre esta categoría`
   * - `editorial_board` (string, opcional): Consejo editorial. Ejemplo: `Lista de editores`
   * - `policy` (string, opcional): Políticas de publicación. Ejemplo: `Políticas editoriales`
   * - `norms` (string, opcional): Normas de la categoría. Ejemplo: `Normas de publicación`
   * - `process` (string, opcional): Proceso de publicación. Ejemplo: `Proceso editorial`
   * - `contact` (string, opcional): Información de contacto. Ejemplo: `editorial@example.com`
   * 
   * ```json
   * {
   *   "title": "Ciencias Exactas",
   *   "description": "Categoría actualizada de ciencias",
   *   "main_category": null,
   *   "is_featured": true,
   *   "about": "Información sobre la categoría",
   *   "editorial_board": "Dr. Juan Pérez, Dra. María López",
   *   "policy": "Políticas de revisión por pares",
   *   "norms": "Normas APA 7",
   *   "process": "Proceso de revisión en 30 días",
   *   "contact": "ciencias@example.com"
   * }
   * ```
   * 
   * ```bash
   * curl -X POST "http://localhost/api/categories/updateCategory/1" \
   *   -H "Accept: application/json" \
   *   -H "Content-Type: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
   *   -d '{"title":"Ciencias Exactas","description":"Categoría actualizada","is_featured":true}'
   * ```
   * 
   * **Nota:** Si se envían campos de detalle (about, editorial_board, etc.), se creará o actualizará el registro en `categories_details`.
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Categoría actualizada correctamente",
   *   "data": {
   *     "id": 1,
   *     "title": "Ciencias Exactas",
   *     "URL": "ciencias_exactas",
   *     "description": "Categoría actualizada de ciencias",
   *     "main_category": null,
   *     "state": 1,
   *     "is_featured": 1,
   *     "language_id": 1,
   *     "category_type": 1,
   *     "created_at": "2025-11-15 10:30:00",
   *     "updated_at": "2025-11-15 12:00:00",
   *     "detail": {
   *       "id": 1,
   *       "category_id": 1,
   *       "language_id": 1,
   *       "about": "Información sobre la categoría",
   *       "editorial_board": "Dr. Juan Pérez, Dra. María López",
   *       "policy": "Políticas de revisión por pares",
   *       "norms": "Normas APA 7",
   *       "process": "Proceso de revisión en 30 días",
   *       "contact": "ciencias@example.com"
   *     }
   *   }
   * }
   * 
   * @response 400 {
   *   "ok": false,
   *   "error": "Bad Request",
   *   "message": "Una categoría no puede ser su propia categoría principal"
   * }
   * 
   * @response 404 {
   *   "ok": false,
   *   "error": "Not Found",
   *   "message": "Categoría no encontrada"
   * }
   * 
   * @response 422 {
   *   "ok": false,
   *   "error": "Validation Error",
   *   "message": "Los datos proporcionados no son válidos",
   *   "errors": {
   *     "title": ["The title field is required."]
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
        throw new BadRequestException('El numero de ID es requerido');
      }

      $updatedCategory = Category::with(['detail'])->findOrFail($id);

      $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255|unique:categories,title,' . $id,
        'URL' => 'string|unique:categories,URL,' . $id,
        'description' => 'string',
        'main_category' => 'integer|nullable',
        'is_featured' => 'boolean',
        'about' => 'string|nullable',
        'editorial_board' => 'string|nullable',
        'policy' => 'string|nullable',
        'norms' => 'string|nullable',
        'process' => 'string|nullable',
        'contact' => 'string|nullable',
        'category_id' => 'integer',
      ]);

      //Si el campo main_category viene vacío, se asigna null (asi se mantienen las categorías padre)
      if ($request->main_category === "") {
        $request->merge(['main_category' => null]);
      }

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      if ($request->has('main_category') && $request->input('main_category') == $id) {
        throw new BadRequestException('Una categoría no puede ser su propia categoría principal');
      }

      DB::transaction(function () use ($id, $request) {
        $category = Category::with(['detail'])->findOrFail($id);

        // Actualizar campos de la tabla categories
        $category->update($request->only([
          'title',
          'description',
          'main_category',
          'is_featured',
          'URL'
        ]));

        // Campos que van a la tabla categories_details
        $detailFields = $request->only([
          'about',
          'editorial_board',
          'policy',
          'norms',
          'process',
          'contact'
        ]);

        // Si hay campos de detalle para actualizar
        if (!empty(array_filter($detailFields))) {
          if ($category->detail) {
            // Si ya existe el detalle, actualizarlo
            $category->detail->update($detailFields);
          } else {
            // Si no existe el detalle, crearlo
            $detailFields['category_id'] = $id;
            $detailFields['language_id'] = 1;
            $category->detail()->create($detailFields);
          }
        }
      });

      // Recargar la categoría con sus detalles actualizados
      $updatedCategory = Category::with(['detail'])->find($id);

      return $this->successResponse($updatedCategory, 'Categoría actualizada correctamente');

    } catch (\Illuminate\Validation\ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las excepciones de API lleguen al Handler
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException('Error al actualizar la categoría', null, $e);
    }
  }

  /**
   * Método privado para desactivar subcategorías recursivamente
   * 
   * @param Category $category Categoría cuyas hijas se desactivarán
   */
  private function deactivateChildren(Category $category)
  {
    foreach ($category->childrenRecursive as $child) {
      if ($child->state) {
        $child->state = 0;
        $child->save();

        if (!$child->childrenRecursive->isEmpty()) {
          $this->deactivateChildren($child);
        }
      }
    }
  }

  /**
   * Método privado para activar subcategorías recursivamente
   * 
   * @param Category $category Categoría cuyas hijas se activarán
   */
  private function activateChildren(Category $category)
  {
    foreach ($category->childrenRecursive as $child) {
      if (!$child->state) {
        $child->state = 1;
        $child->save();

        if (!$child->childrenRecursive->isEmpty()) {
          $this->activateChildren($child);
        }
      }
    }
  }

  /**
   * Activar/Desactivar categoría
   * 
   * Alterna el estado de una categoría (activa/inactiva). 
   * Si es una categoría padre, también afecta recursivamente a todas sus subcategorías.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros de ruta (URL Parameters):**
   * - `id` (integer, requerido): ID de la categoría cuyo estado se alternará. Ejemplo: `1`
   * 
   * ```bash
   * curl -X POST "http://localhost/api/categories/toggleState/1" \
   *   -H "Accept: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
   * ```
   * 
   * **Comportamiento:**
   * - Si la categoría está **activa** y tiene subcategorías: se desactiva junto con todas sus subcategorías recursivamente
   * - Si la categoría está **inactiva** y tiene subcategorías: se activa junto con todas sus subcategorías recursivamente
   * - Si es una categoría sin hijas: solo cambia su propio estado
   * 
   * **Nota importante:** Esta operación usa transacciones de base de datos para garantizar la consistencia.
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Categoría activada correctamente",
   *   "data": {
   *     "id": 1,
   *     "title": "Ciencias",
   *     "description": "Categoría de ciencias",
   *     "main_category": null,
   *     "state": 1,
   *     "is_featured": 0,
   *     "URL": "ciencias",
   *     "language_id": 1,
   *     "category_type": 1,
   *     "created_at": "2025-11-15 10:30:00",
   *     "updated_at": "2025-11-15 12:30:00",
   *     "children_recursive": [
   *       {
   *         "id": 2,
   *         "title": "Física",
   *         "state": 1,
   *         "main_category": 1
   *       }
   *     ]
   *   }
   * }
   * 
   * @response {
   *   "ok": true,
   *   "message": "Categoría y sus subcategorías desactivadas correctamente",
   *   "data": {
   *     "id": 1,
   *     "title": "Ciencias",
   *     "description": "Categoría de ciencias",
   *     "main_category": null,
   *     "state": 0,
   *     "is_featured": 0,
   *     "URL": "ciencias",
   *     "language_id": 1,
   *     "category_type": 1,
   *     "created_at": "2025-11-15 10:30:00",
   *     "updated_at": "2025-11-15 12:30:00",
   *     "children_recursive": [
   *       {
   *         "id": 2,
   *         "title": "Física",
   *         "state": 0,
   *         "main_category": 1
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
   *   "message": "Categoría no encontrada"
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
        throw new BadRequestException('El numero de ID es requerido');
      }

      DB::beginTransaction();

      $category = Category::with('childrenRecursive')->findOrFail($id);

      // Si es una categoría padre y se va a desactivar
      if (!$category->childrenRecursive->isEmpty()) {
        if ($category->state) {
          $this->deactivateChildren($category);
        } else {
          $this->activateChildren($category);
        }
      }

      // Alternar el estado de la categoría principal
      $category->state = !$category->state;
      $category->save();

      DB::commit();

      $message = $category->state
        ? 'Categoría activada correctamente'
        : 'Categoría y sus subcategorías desactivadas correctamente';

      return $this->successResponse($category, $message);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      DB::rollBack();
      throw new \App\Exceptions\NotFoundException('Categoría no encontrada');
    } catch (\App\Exceptions\ApiException $e) {
      DB::rollBack();
      throw $e;
    } catch (Exception $e) {
      DB::rollBack();
      throw new ServerErrorException('Error al cambiar el estado de la categoría', null, $e);
    }
  }

}
