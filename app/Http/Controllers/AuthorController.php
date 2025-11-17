<?php

namespace App\Http\Controllers;


use App\Author;
use App\Http\Traits\SearchFilter;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerErrorException;

/**
 * @resource Authors
 *
 * Endpoints para la gestión de autores.
 * Usa JWT en el header: `Authorization: Bearer {token}`.
 */
class AuthorController extends Controller
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

  /**
   * Listar autores
   * 
   * Obtiene una lista paginada de autores con opciones de búsqueda y ordenamiento.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros de consulta (Query Parameters):**
   * - `page` (integer, opcional): Número de página. Por defecto: 1. Ejemplo: `1`
   * - `limit` (integer, opcional): Resultados por página (mínimo 10). Por defecto: 10. Ejemplo: `10`
   * - `search` (string, opcional): Búsqueda por nombre o apellido. Ejemplo: `García`
   * - `sort` (string, opcional): Campo para ordenar (id, name, lastname). Ejemplo: `name:asc`
   * 
   * ```bash
   * curl -X GET "http://localhost/api/authors/getAuthors?page=1&limit=10&search=García" \
   *   -H "Accept: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
   * ```
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Autores obtenidos exitosamente",
   *   "data": [
   *     {
   *       "id": 1,
   *       "name": "Juan",
   *       "lastname": "García",
   *       "created_at": "2025-11-15 10:30:00",
   *       "updated_at": "2025-11-15 10:30:00"
   *     },
   *     {
   *       "id": 2,
   *       "name": "María",
   *       "lastname": "López",
   *       "created_at": "2025-11-15 11:00:00",
   *       "updated_at": "2025-11-15 11:00:00"
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
   *     "limit": ["The limit must be at least 10."]
   *   }
   * }
   */
  public function index(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'page' => 'integer|min:1',
        'limit' => 'integer|min:10',
        'search' => 'string|nullable'
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $limit = $request->query('limit', 10);
      $search = $request->query('search', null);

      $query = Author::with([]);

      if ($search) {
        $query->where(function ($q) use ($search) {
          $q->where('name', 'like', '%' . $search . '%')
            ->orWhere('lastname', 'like', '%' . $search . '%');
        });
      }

      $allowedSortFields = ['id', 'name', 'lastname'];

      if ($request->has('sort')) {
        $this->search($request, $query, $allowedSortFields);
      } else {
        $query->orderBy('id', 'desc');
      }

      $authors = $query->paginate($limit);
      $pagination = [
        'total' => $authors->total(),
        'current_page' => $authors->currentPage(),
        'last_page' => $authors->lastPage(),
        'per_page' => $authors->perPage(),
        'from' => $authors->firstItem(),
        'to' => $authors->lastItem()
      ];

      return $this->successResponse($authors->items(), 'Autores obtenidos exitosamente', $pagination);

    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las ApiException se manejen en el Handler
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al obtener los autores', null, $e);
    }
  }

  /**
   * Obtener un autor
   * 
   * Obtiene la información detallada de un autor específico por su ID.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros de ruta (URL Parameters):**
   * - `id` (integer, requerido): ID del autor a consultar. Ejemplo: `1`
   * 
   * ```bash
   * curl -X GET "http://localhost/api/authors/getAuthor/1" \
   *   -H "Accept: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
   * ```
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Autor obtenido exitosamente",
   *   "data": {
   *     "id": 1,
   *     "name": "Juan",
   *     "lastname": "García",
   *     "created_at": "2025-11-15 10:30:00",
   *     "updated_at": "2025-11-15 10:30:00"
   *   }
   * }
   * 
   * @response 404 {
   *   "ok": false,
   *   "error": "Not Found",
   *   "message": "Autor no encontrado"
   * }
   * 
   * @response 401 {
   *   "message": "Unauthenticated"
   * }
   */
  public function find($id)
  {
    try {
      $author = Author::find($id);

      if (!$author) {
        throw new NotFoundException('Autor no encontrado');
      }

      return $this->successResponse($author, 'Autor obtenido exitosamente');
    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las ApiException se manejen en el Handler
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al obtener el autor', null, $e);
    }
  }

  /**
   * Crear un autor
   * 
   * Crea un nuevo autor en el sistema.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros del Body:**
   * - `name` (string, requerido): Nombre del autor (máximo 255 caracteres). Ejemplo: `Juan`
   * - `lastname` (string, requerido): Apellido del autor (máximo 255 caracteres). Ejemplo: `García`
   * 
   * ```json
   * {
   *   "name": "Juan",
   *   "lastname": "García"
   * }
   * ```
   * 
   * ```bash
   * curl -X POST "http://localhost/api/authors/createAuthor" \
   *   -H "Accept: application/json" \
   *   -H "Content-Type: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
   *   -d '{"name":"Juan","lastname":"García"}'
   * ```
   * 
   * @authenticated
   * 
   * @response 201 {
   *   "ok": true,
   *   "message": "Autor creado exitosamente",
   *   "data": {
   *     "id": 1,
   *     "name": "Juan",
   *     "lastname": "García",
   *     "created_at": "2025-11-15 10:30:00",
   *     "updated_at": "2025-11-15 10:30:00"
   *   }
   * }
   * 
   * @response 409 {
   *   "ok": false,
   *   "error": "Conflict",
   *   "message": "El autor enviado ya existe"
   * }
   * 
   * @response 422 {
   *   "ok": false,
   *   "error": "Validation Error",
   *   "message": "Los datos proporcionados no son válidos",
   *   "errors": {
   *     "name": ["The name field is required."],
   *     "lastname": ["The lastname field is required."]
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
        'name' => 'required|string|max:255',
        'lastname' => 'required|string|max:255',
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      if (Author::alreadyExists($request->input('name'), $request->input('lastname'))) {
        throw new ConflictException('El autor enviado ya existe');
      }

      $author = Author::create($request->only('name', 'lastname'));

      return $this->successResponse($author, 'Autor creado exitosamente', null, 201);

    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las ApiException se manejen en el Handler
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al crear el autor', null, $e);
    }
  }

  /**
   * Actualizar un autor
   * 
   * Actualiza la información de un autor existente.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros de ruta (URL Parameters):**
   * - `id` (integer, requerido): ID del autor a actualizar. Ejemplo: `1`
   * 
   * **Parámetros del Body:**
   * - `name` (string, requerido): Nuevo nombre del autor (máximo 255 caracteres). Ejemplo: `Juan Carlos`
   * - `lastname` (string, requerido): Nuevo apellido del autor (máximo 255 caracteres). Ejemplo: `García Pérez`
   * 
   * ```json
   * {
   *   "name": "Juan Carlos",
   *   "lastname": "García Pérez"
   * }
   * ```
   * 
   * ```bash
   * curl -X POST "http://localhost/api/authors/updateAuthor/1" \
   *   -H "Accept: application/json" \
   *   -H "Content-Type: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
   *   -d '{"name":"Juan Carlos","lastname":"García Pérez"}'
   * ```
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Autor actualizado exitosamente",
   *   "data": {
   *     "id": 1,
   *     "name": "Juan Carlos",
   *     "lastname": "García Pérez",
   *     "created_at": "2025-11-15 10:30:00",
   *     "updated_at": "2025-11-15 12:00:00"
   *   }
   * }
   * 
   * @response 404 {
   *   "ok": false,
   *   "error": "Not Found",
   *   "message": "Autor no encontrado"
   * }
   * 
   * @response 409 {
   *   "ok": false,
   *   "error": "Conflict",
   *   "message": "El autor enviado ya existe"
   * }
   * 
   * @response 422 {
   *   "ok": false,
   *   "error": "Validation Error",
   *   "message": "Los datos proporcionados no son válidos",
   *   "errors": {
   *     "name": ["The name field is required."],
   *     "lastname": ["The lastname field is required."]
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

      if (!Author::where('id', $id)->exists()) {
        throw new NotFoundException('Autor no encontrado');
      }

      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'lastname' => 'required|string|max:255',
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $author = Author::find($request->input('id'));

      if (Author::alreadyExists($request->input('name'), $request->input('lastname'))) {
        throw new ConflictException('El autor enviado ya existe');
      }

      $author->update($request->only('name', 'lastname'));

      return $this->successResponse($author, 'Autor actualizado exitosamente');

    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las ApiException se manejen en el Handler
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al actualizar el autor', null, $e);
    }
  }

  /**
   * Eliminar un autor
   * 
   * Elimina un autor del sistema. También elimina las relaciones del autor con las publicaciones.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros de ruta (URL Parameters):**
   * - `id` (integer, requerido): ID del autor a eliminar. Ejemplo: `1`
   * 
   * ```bash
   * curl -X POST "http://localhost/api/authors/deleteAuthor/1" \
   *   -H "Accept: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
   * ```
   * 
   * **Nota:** Esta operación eliminará el autor y todas sus relaciones con publicaciones, pero no eliminará las publicaciones mismas.
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Autor eliminado exitosamente",
   *   "data": null
   * }
   * 
   * @response 404 {
   *   "ok": false,
   *   "error": "Not Found",
   *   "message": "Autor no encontrado"
   * }
   * 
   * @response 422 {
   *   "ok": false,
   *   "error": "Validation Error",
   *   "message": "Los datos proporcionados no son válidos",
   *   "errors": {
   *     "id": ["The selected id is invalid."]
   *   }
   * }
   * 
   * @response 401 {
   *   "message": "Unauthenticated"
   * }
   */
  public function delete($id = null)
  {
    try {

      $validator = Validator::make(['id' => $id], [
        'id' => 'required|integer|exists:authors,id',
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $author = Author::find($id);

      if (!$author) {
        throw new NotFoundException('Autor no encontrado');
      }

      $author->publishings()->detach();
      $author->delete();

      return $this->successResponse(null, 'Autor eliminado exitosamente');

    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al eliminar el autor', null, $e);
    }
  }
}


