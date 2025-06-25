<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Publishing;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;

class PublishingController extends Controller
{
    public function index(Request $request) {
        try {
            $validator = \Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'limit' => 'integer|min:10',
                'publishing_type' => 'integer'
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }
        
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            $publishing_type = $request->query('publishing_type', null);

            $publishings = Publishing::with(['type'])
                ->when($publishing_type, function ($query) use ($publishing_type) {
                    return $query->where('publishing_type', $publishing_type);
                })
                ->paginate($limit);

            return response()->json([
                'success' => true,
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
                'success' => false,
                'message' => 'Error al obtener las publicaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}