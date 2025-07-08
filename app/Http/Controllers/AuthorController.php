<?php

namespace App\Http\Controllers;


use App\Author;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;

class AuthorController extends Controller
{

  use ApiResponse;

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
        'search' => 'string'
      ]);

      if ($validator->fails()) {
        return $this->errorResponse('Parámetros inválidos', 422, $validator->errors());
      }

      $limit = $request->query('limit', 10);
      $search = $request->query('search', null);

      $query = Author::getModel();

      if ($search) {
        $query->where('name', 'like', '%' . $search . '%');
      }

      $allowedSortFields = ['id', 'name', 'lastname'];

      if ($request->has('sort')) {
        $sorts = explode(',', $request->input('sort'));
        $directions = $request->has('direction')
          ? explode(',', $request->input('direction'))
          : [];
        foreach ($sorts as $index => $sortField) {
          if (!in_array($sortField, $allowedSortFields)) {
            return response()->json([
              'success' => false,
              'message' => "Campo de ordenamiento no permitido: {$sortField}",
              'allowed_fields' => $allowedSortFields
            ], 422);
          }

          $direction = isset($directions[$index])
            ? (strtolower($directions[$index]) === 'desc' ? 'desc' : 'asc')
            : 'asc';

          $query->orderBy($sortField, $direction);
        }
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
}