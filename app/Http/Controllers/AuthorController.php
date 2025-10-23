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
 * Gestión de autores del sistema editorial.
 */
class AuthorController extends Controller
{

  use ApiResponse;
  use SearchFilter;
  public function __construct()
  {
    $this->middleware('auth:api');
  }
 /**
     * Listar autores
     *
     * Devuelve un listado paginado de autores con opciones de búsqueda y ordenamiento.
     *
     * @group Authors
     * @queryParam page integer Número de página a mostrar. Example: 1
     * @queryParam limit integer Cantidad de resultados por página (mínimo 10). Example: 15
     * @queryParam search string Termino a buscar en nombre o apellido. Example: García
     * @queryParam sort string Campo de ordenamiento permitido (`id`, `name`, `lastname`), se puede acompañar de sufijo `:desc`. Example: name:asc
     *
     * @response {
     *   "ok": true,
     *   "message": "Autores obtenidos exitosamente",
     *   "data": [
     *     {
     *       "id": 114,
     *       "name": "asdasd",
     *       "lastname": "asdasdssss"
     *     },
     *     {
     *       "id": 113,
     *       "name": "Dross",
     *       "lastname": "Rotzank"
     *     },
     *     {
     *       "id": 109,
     *       "name": "Emilio",
     *       "lastname": "Delgado Chavarría"
     *     },
     *     {
     *       "id": 108,
     *       "name": "Humberto",
     *       "lastname": "Flores"
     *     },
     *     {
     *       "id": 106,
     *       "name": "René",
     *       "lastname": "Mauricio Valdéz"
     *     },
     *     {
     *       "id": 105,
     *       "name": "Claudia",
     *       "lastname": "Urbina"
     *     }
     *   ],
     *   "pagination": {
     *     "total": 114,
     *     "current_page": 1,
     *     "last_page": 12,
     *     "per_page": "10",
     *     "from": 1,
     *     "to": 10
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
     * Obtener autor
     *
     * Devuelve la información de un autor específico.
     *
     * @group Authors
     * @urlParam id integer required Identificador del autor. Example: 5
     *
     * @response {
     *  "ok": true,
     *  "message": "Autor obtenido exitosamente",
     *  "data": {
     *      "id": 1,
     *      "name": "Ana Cecilia equisde",
     *      "lastname": "de Méndez"
     *  },
     *  "pagination": null
     * }
     * @response 404 {
     *   "ok": false,
     *   "error": "NotFoundException",
     *   "message": "Autor no encontrado"
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
    } catch (\Illuminate\Validation\ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las ApiException se manejen en el Handler
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al obtener el autor', null, $e);
    }
  }
 /**
     * Crear autor
     *
     * Registra un nuevo autor.
     * > Body Esperado
     *
     * Body esperado:
     * ```json
     * {
     *   "name": "Julio",
     *   "lastname": "Cortázar"
     * }
     * ```
     *  
     * @group Authors
     * @bodyParam name string required Nombre del autor. Example: Julio
     * @bodyParam lastname string required Apellido del autor. Example: Cortázar
     *
     * @response {
     *    "ok": true,
     *    "message": "Autor creado exitosamente",
     *    "data": {
     *        "name": "Robert",
     *        "lastname": "Martin",
     *        "id": 115
     *    },
     *    "pagination": null
     * }
     * @response 409 {
     *   "ok": false,
     *   "error": "ConflictException",
     *   "message": "El autor enviado ya existe"
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

    } catch (\Illuminate\Validation\ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las ApiException se manejen en el Handler
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al crear el autor', null, $e);
    }
  }

  public function update(Request $request)
  {
    /**
     * Actualizar autor
     *
     * Modifica los datos de un autor existente.
     * > Body Esperado
     *
     * Body esperado
     * ```json
     * {
     *   "id": 1,
     *   "name": "Robert C.",
     *   "lastname": "Martin"
     * }
     * ```
     *
     * @group Authors
     * @bodyParam id integer required Identificador del autor. Example: 12
     * @bodyParam name string required Nombre del autor. Example: Laura
     * @bodyParam lastname string required Apellido del autor. Example: Esquivel
     *
     * @response {
     *  "ok": true,
     *  "message": "Autor actualizado exitosamente",
     *  "data": {
     *      "id": 1,
     *      "name": "Robert C.",
     *      "lastname": "Martin"
     *  },
     *  "pagination": null
     * }
     * @response 404 {
     *   "ok": false,
     *   "error": "NotFoundException",
     *   "message": "Autor no encontrado"
     * }
     */
    try {

      $validator = Validator::make($request->all(), [
        'id' => 'required|integer|exists:authors,id',
        'name' => 'required|string|max:255',
        'lastname' => 'required|string|max:255',
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $author = Author::find($request->input('id'));

      if (!$author) {
        throw new NotFoundException('Autor no encontrado');
      }

      if (Author::alreadyExists($request->input('name'), $request->input('lastname'))) {
        throw new ConflictException('El autor enviado ya existe');
      }

      $author->update($request->only('name', 'lastname'));

      return $this->successResponse($author, 'Autor actualizado exitosamente');

    } catch (\Illuminate\Validation\ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las ApiException se manejen en el Handler
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al actualizar el autor', null, $e);
    }
  }

  public function delete($id)
    {
        /**
         * Eliminar autor
         *
         * Endpoint reservado para completar la eliminación de autores.
         *
         * @group Authors
         * @urlParam id integer required Identificador del autor. Example: 8
         *
         * @response 501 {
         *   "ok": false,
         *   "message": "Función no implementada aún."
         * }
         */
        return $this->errorResponse('Función no implementada aún.', 501);
    }
}
