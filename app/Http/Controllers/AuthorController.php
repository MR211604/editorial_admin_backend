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

class AuthorController extends Controller
{

  use ApiResponse;
  use SearchFilter;
  public function __construct()
  {
    $this->middleware('auth:api');
  }

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

  public function update(Request $request)
  {
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

    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las ApiException se manejen en el Handler
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al actualizar el autor', null, $e);
    }
  }
}
