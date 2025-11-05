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
      $categories = in_array($request->query('getAll'), ['1', 'true', true], true)
        ? $query->get(['id', 'title', 'description', 'category_type', 'state'])
        : $query->with('childrenRecursive')
          ->whereNull('main_category')
          ->get(['id', 'title', 'description', 'category_type', 'state']);

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

  public function find($id = null)
  {
    try {
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
        throw new ValidationException($validator);
      }

      if (Category::alreadyExists($request->input('title'))) {
        throw new ConflictException('La categoría ya existe');
      }

      $category = Category::create([
        'title' => $request->input('title'),
        'description' => $request->input('description'),
        'main_category' => null, // Por defecto, se crea como categoría padre
        'state' => 1, //Se crea como categoria activa
        'is_featured' => 0,
        'language_id' => $request->input('language_id', 1), // Por defecto, idioma 1
        'category_type' => $request->input('category_type'),
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

  public function update(Request $request, $id = null)
  {
    try {

      if (empty($id) || !is_numeric($id)) {
        throw new BadRequestException('El numero de ID es requerido');
      }

      $updatedCategory = Category::with(['detail'])->findOrFail($id);

      $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
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

    } catch (\Illuminate\Validation\ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las excepciones de API lleguen al Handler
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException('Error al actualizar la categoría', null, $e);
    }
  }

  public function toggleState(Request $request, $id = null)
  {
    try {

      if (empty($id) || !is_numeric($id)) {
        throw new BadRequestException('El numero de ID es requerido');
      }

      $category = Category::findOrFail($id);

      // Alternar el estado
      $category->state = !$category->state;
      $category->save();

      $message = $category->state ? 'Categoría activada correctamente' : 'Categoría desactivada correctamente';

      return $this->successResponse($category, $message);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      throw new \App\Exceptions\NotFoundException('Categoría no encontrada');
    } catch (\App\Exceptions\ApiException $e) {
      // Dejar que las excepciones de API lleguen al Handler
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException('Error al cambiar el estado de la categoría', null, $e);
    }
  }

}
