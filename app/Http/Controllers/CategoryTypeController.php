<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CategoryType;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ApiResponse;
use Illuminate\Validation\ValidationException;
use App\Exceptions\ServerErrorException;

class CategoryTypeController extends Controller
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
                'limit' => 'integer|min:10'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $limit = $request->query('limit', 10);

            $categoryTypes = CategoryType::paginate($limit);

            $data = [
                'category_types' => $categoryTypes->items(),
                'pagination' => [
                    'current_page' => $categoryTypes->currentPage(),
                    'last_page' => $categoryTypes->lastPage(),
                    'per_page' => $categoryTypes->perPage(),
                    'total' => $categoryTypes->total(),
                    'from' => $categoryTypes->firstItem(),
                    'to' => $categoryTypes->lastItem(),
                ]
            ];

            return $this->successResponse($data['category_types'], 'Tipos de categoría obtenidos exitosamente', $data['pagination']);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            // Dejar que las excepciones de API lleguen al Handler
            throw $e;
        } catch (\Exception $e) {
            throw new ServerErrorException('Error al obtener los tipos de categoría', null, $e);
        }
    }

}
