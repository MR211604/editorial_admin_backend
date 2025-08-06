<?php
namespace App\Http\Controllers;

use App\Category;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;

class CategoryController extends Controller
{


  use ApiResponse;

  public function __construct()
  {
    $this->middleware('auth:api');
  }

  public function index(Request $request)
  {
    try {

      $query = Category::getModel();

      //Obteniendo las categorías padres con las hijas
      $categories = $request->query('getAll') === '1'
        ? $query->get(['id', 'title', 'description', 'category_type'])
        : $query->with('childrenRecursive')
          ->whereNull('main_category')
          ->get(['id', 'title', 'description', 'category_type']);

      return $this->successResponse($categories, 'Categorías obtenidas correctamente');

    } catch (Exception $e) {
      return $this->errorResponse('Error al obtener las categorías', 500, $e->getMessage());
    }
  }

  public function find($id = null)
  {
    try {
      $category = Category::with(['detail'])->findOrFail($id);
      return $this->successResponse($category, 'Categoría obtenida correctamente');
    } catch (Exception $e) {
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
        'category_type' => $request->input('category_type'),
      ]);

      return $this->successResponse($category, 'Categoría creada correctamente');

    } catch (Exception $e) {
      return $this->errorResponse('Error al crear la categoría ' . $e->getMessage(), 500);
    }

  }

  public function update(Request $request, $id = null)
  {
    try {

      if (empty($id) || !is_numeric($id)) {
        return $this->errorResponse('El numero de ID es requerido', 400);
      }

      $updatedCategory = Category::with(['detail'])->findOrFail($id);

      $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'description' => 'string',
        'main_category' => 'nullable|integer',
        'is_featured' => 'boolean',
        'about' => 'string|nullable',
        'editorial_board' => 'string|nullable',
        'policy' => 'string|nullable',
        'norms' => 'string|nullable',
        'process' => 'string|nullable',
        'contact' => 'string|nullable',
        'category_id' => 'integer',
      ]);

      if ($validator->fails()) {
        return $this->errorResponse('Parámetros inválidos', 422, $validator->errors());
      }

      DB::transaction(function () use ($id, $request) {
        $category = Category::with(['detail'])->findOrFail($id);

        // Actualizar campos de la tabla categories
        $category->update($request->only([
          'title',
          'description',
          'main_category',
          'is_featured'
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

    } catch (Exception $e) {
      return $this->errorResponse('Error al actualizar la categoría', 500, $e->getMessage());
    }
  }

}