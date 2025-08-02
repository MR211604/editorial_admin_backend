<?php
namespace App\Http\Controllers;

use App\Category;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{


  use ApiResponse;

  public function index(Request $request)
  {
    try {

      $query = Category::getModel();

      //Obteniendo las categorías padres con las hijas
      $categories = $request->query('getAll') === '1'
        ? $query->get(['id', 'title', 'description'])
        : $query->whereNull('main_category')
          ->with(['children:id,title,description,main_category'])
          ->get(['id', 'title', 'description']);

      return $this->successResponse($categories, 'Categorías obtenidas correctamente');

    } catch (\Exception $e) {
      return $this->errorResponse('Error al obtener las categorías', 500, $e->getMessage());
    }
  }

  public function find($id = null)
  {
    try {
      $category = Category::with(['detail'])->findOrFail($id);
      return $this->successResponse($category, 'Categoría obtenida correctamente');
    } catch (\Exception $e) {
      return $this->errorResponse('Error al obtener la categoría', 404, $e->getMessage());
    }
  }

  public function create(Request $request)
  {

    try {

      //!TODO: AGREGAR LANGUAGE ID DESPUES
      $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'description' => 'string',
        'category_type' => 'integer' // Se crea como padre por defecto        
      ]);

      if ($validator->fails()) {
        return $this->errorResponse('Parámetros inválidos', 422, $validator->errors());
      }

      if (Category::alreadyExists($request->input('title'))) {
        return $this->errorResponse('La categoría ya existe', 409);
      }

      $category = Category::create([
        'title' => $request->input('title'),
        'description' => $request->input('description'),
        'main_category' => null, // Por defecto, se crea como categoría padre
        'state' => 0,
        'is_featured' => 0,
        'language_id' => $request->input('language_id', 1), // Por defecto, idioma 1
        'category_type' => $request->input('category_type'), // Por defecto, tipo 1
      ]);

      return $this->successResponse($category, 'Categoría creada correctamente');

    } catch (\Exception $e) {
      return $this->errorResponse('Error al crear la categoría ' . $e->getMessage(), 500);
    }

  }

}