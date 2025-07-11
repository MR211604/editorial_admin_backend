<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Publishing;
use Illuminate\Support\Facades\Validator;

class PublishingController extends Controller
{
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

            $query = Publishing::with(['type', 'authors']);

            if ($search) {
                $query->where('title', 'like', '%' . $search . '%');                      
            }

            if ($publishing_type) {
                $query->where('publishing_type', $publishing_type);
            }

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
                // Ordenamiento por defecto
                $query->orderBy('id', 'desc');
            }

            $publishings = $query->paginate($limit);

            return response()->json([
                'ok' => true,
                'data' => $publishings->items(),
                'pagination' => [
                    'current_page' => $publishings->currentPage(),
                    'last_page' => $publishings->lastPage(),
                    'per_page' => $publishings->perPage(),
                    'total' => $publishings->total(),
                    'from' => $publishings->firstItem(),
                    'to' => $publishings->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Error al obtener las publicaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}