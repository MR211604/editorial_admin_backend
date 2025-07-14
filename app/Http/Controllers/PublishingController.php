<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Http\Traits\SearchFilter;
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
                return response()->json([
                    'ok' => false,
                    'message' => 'Parámetros inválidos',
                    'errors' => $validator->errors()
                ], 422);
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

}