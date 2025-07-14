<?php

namespace App\Http\Controllers;


use App\Author;
use App\Http\Traits\SearchFilter;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;

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
        return $this->errorResponse('Parámetros inválidos', 422, $validator->errors());
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

    } catch (\Exception $e) {
      return $this->errorResponse('Error al obtener los autores', 500);
    }
  }

  public function find($id)
  {
    try {
      $author = Author::find($id);

      if (!$author) {
        return $this->errorResponse('Autor no encontrado', 404);
      }

      return $this->successResponse($author, 'Autor obtenido exitosamente');
    } catch (\Exception $e) {
      return $this->errorResponse('Error al obtener el autor', 500);
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
        return $this->errorResponse('Parámetros inválidos', 422, $validator->errors());
      }

      if (Author::alreadyExists($request->input('name'), $request->input('lastname'))) {
        return $this->errorResponse('El autor enviado ya existe', 409);
      }

      $author = Author::create($request->only('name', 'lastname'));

      return $this->successResponse($author, 'Autor creado exitosamente', 201);

    } catch (\Exception $e) {
      return $this->errorResponse('Error al crear el autor: ' . $e->getMessage(), 500);
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
        return $this->errorResponse('Parámetros inválidos', 422, $validator->errors());
      }

      $author = Author::find($request->input('id'));

      if (!$author) {
        return $this->errorResponse('Autor no encontrado', 404);
      }

      if (Author::alreadyExists($request->input('name'), $request->input('lastname'))) {
        return $this->errorResponse('El autor enviado ya existe', 409);
      }

      $author->update($request->only('name', 'lastname'));

      return $this->successResponse($author, 'Autor actualizado exitosamente');

    } catch (\Exception $e) {
      return $this->errorResponse('Error al actualizar el autor: ' . $e->getMessage(), 500);
    }
  }
}