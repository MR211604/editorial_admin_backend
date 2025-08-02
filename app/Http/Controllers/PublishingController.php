<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Http\Traits\SearchFilter;
use DB;
use Illuminate\Http\Request;
use App\Publishing;
use Illuminate\Support\Facades\Validator;

class PublishingController extends Controller
{

    use ApiResponse;
    use SearchFilter;

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'limit' => 'integer|min:10',
                'publishing_type' => 'integer'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Parámetros inválidos', 422, $validator->errors());
            }

            $allowedSortFields = ['id', 'title', 'created_at', 'updated_at', 'publishing_type'];
            // $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            $publishing_type = $request->query('publishing_type', null);
            $search = $request->query('search', null);
            $category = $request->query('category', null);

            $query = Publishing::with(['type', 'authors', 'details']);

            if ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            }

            if ($category) {
                $query->where('category_id', $category);
            }

            if ($publishing_type) {
                $query->where('publishing_type', $publishing_type);
            }

            if ($request->has('sort')) {
                $this->search($request, $query, $allowedSortFields);
            } else {
                // Ordenamiento por defecto
                $query->orderBy('id', 'desc');
            }

            $publishings = $query->paginate($limit);

            $pagination = [
                'total' => $publishings->total(),
                'current_page' => $publishings->currentPage(),
                'last_page' => $publishings->lastPage(),
                'per_page' => $publishings->perPage(),
                'from' => $publishings->firstItem(),
                'to' => $publishings->lastItem(),
            ];

            return $this->successResponse($publishings->items(), 'Publicaciones obtenidas correctamente', $pagination, 200);

        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener las publicaciones', 500);
        }
    }

    public function find($id = null)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                return $this->errorResponse('El numero de ID es requerido', 400);
            }

            if (!Publishing::where('id', $id)->exists()) {
                return $this->errorResponse('Publicación no encontrada', 404);
            }

            $publishing = Publishing::with(['type', 'authors', 'details'])->findOrFail($id);

            return $this->successResponse($publishing, 'Publicación obtenida exitosamente');

        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener la publicación: ' . $e->getMessage(), 500);
        }
    }

    public function create(Request $request)
    {
        return $this->errorResponse('Método no implementado', 501);
    }

    public function update(Request $request, $id = null)
    {

        try {

            if (empty($id) || !is_numeric($id)) {
                return $this->errorResponse('El numero de ID es requerido', 400);
            }

            $updatedPublishing = Publishing::with(['authors', 'category', 'type', 'details'])->findOrFail($id);

            //Operacion para los libros            
            if ($request->has('publishing_type') && $request->input('publishing_type') == 1) {
                if (!Publishing::where('id', $id)->where('publishing_type', 1)->exists()) {
                    return $this->errorResponse('Publicación no encontrada', 404);
                }

                $validator = Validator::make($request->all(), [
                    'publishing_type' => 'required|integer|exists:publishing_types,id',
                    'title' => 'required|string|max:255',
                    'pages' => 'required|integer|min:1',
                    'publish_date' => 'required|date',
                    'publish_code' => 'required|integer|min:1',
                    'category_id' => 'nullable|integer|exists:categories,id',
                    'is_new_arrival' => 'boolean',
                    'URL' => 'nullable|string',
                    'authors' => 'array',
                    'authors.*' => 'integer|exists:authors,id',
                ]);

                if ($validator->fails()) {
                    return $this->errorResponse('Parámetros inválidos', 422, $validator->errors());
                }

                DB::transaction(function () use ($id, $request) {
                    // Buscar la publicación con algunas relaciones
                    $publishing = Publishing::with(['category', 'type', 'details'])->findOrFail($id);

                    // Actualizar atributos principales
                    $publishing->update($request->only([
                        'publishing_type',
                        'title',
                        'pages',
                        'publish_date',
                        'publish_code',
                        'category_id',
                        'is_new_arrival',
                        'URL'
                    ]));

                    // Sincronizar autores
                    $publishing->authors()->sync($request->input('authors'));
                });

            }

            //Operacion para las revistas
            if ($request->has('publishing_type') && $request->input('publishing_type') == 2) {

                //Esta validacion asegura que la publicación sea del tipo revista
                if (!Publishing::where('id', $id)->where('publishing_type', 2)->exists()) {
                    return $this->errorResponse('Publicación no encontrada', 404);
                }

                $validator = Validator::make($request->all(), [
                    'title' => 'required|string|max:255',
                    'pages' => 'required|integer|min:1',
                    'publish_date' => 'required|date',
                    'publish_code' => 'required|integer|min:1',
                    'age' => 'nullable|integer|min:1',
                    'age_number' => 'nullable|integer|min:1',
                    'volume' => 'nullable|integer|min:1',
                    'is_new_arrival' => 'boolean',
                    'category_id' => 'nullable|integer|exists:categories,id',
                    'publishing_type' => 'required|integer|exists:publishing_types,id',
                    'authors' => 'array',
                    'authors.*' => 'integer|exists:authors,id',
                ]);

                if ($validator->fails()) {
                    return $this->errorResponse('Parámetros inválidos', 422, $validator->errors());
                }

                // Usar transacción para asegurar integridad de datos
                DB::transaction(function () use ($id, $request) {
                    // Buscar la publicación con algunas relaciones
                    $publishing = Publishing::with(['category', 'type', 'details'])->findOrFail($id);

                    // Actualizar atributos principales
                    $publishing->update($request->only([
                        'title',
                        'publish_date',
                        'publish_code',
                        'age',
                        'age_number',
                        'pages',
                        'volume',
                        'is_new_arrival',
                        'category_id',
                        'publishing_type'
                    ]));

                    // Sincronizar autores
                    $publishing->authors()->sync($request->input('authors'));
                });
            }

            return $this->successResponse($updatedPublishing, 'Publicación actualizada exitosamente', 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Publicación no encontrada', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar la publicación: ' . $e->getMessage(), 500);
        }
    }

}