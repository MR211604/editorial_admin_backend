<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CategoryType;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ApiResponse;
use Illuminate\Validation\ValidationException;
use App\Exceptions\ServerErrorException;

/**
 * @resource Category Types
 *
 * Endpoints para la gestión de tipos de categorías.
 * Usa JWT en el header: `Authorization: Bearer {token}`.
 */
class CategoryTypeController extends Controller
{
    use ApiResponse;

    /**
     * Constructor (middleware)
     *
     * Aplica el middleware `auth:api` a todas las rutas.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Listar tipos de categoría
     * 
     * Obtiene todos los tipos de categoría disponibles en el sistema.
     * 
     * **Autenticación requerida:**
     * Este endpoint requiere un token JWT válido en el header de autorización.
     * 
     * **Parámetros de consulta (Query Parameters):**
     * - `page` (integer, opcional): Número de página (mínimo 1). Ejemplo: `1`
     * - `limit` (integer, opcional): Resultados por página (mínimo 10). Ejemplo: `10`
     * 
     * ```bash
     * curl -X GET "http://localhost/api/categoryTypes/getCategoryTypes" \
     *   -H "Accept: application/json" \
     *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
     * ```
     * 
     * **Nota:** Actualmente devuelve todos los tipos sin paginación. 
     * Si se requiere paginación en el futuro, revisar el código comentado en el controlador.
     * 
     * @authenticated
     * 
     * @response {
     *   "ok": true,
     *   "message": "Tipos de categoría obtenidos exitosamente",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Revista",
     *       "description": "Publicaciones periódicas",
     *       "created_at": "2025-11-15 10:30:00",
     *       "updated_at": "2025-11-15 10:30:00"
     *     },
     *     {
     *       "id": 2,
     *       "name": "Libro",
     *       "description": "Publicaciones en formato libro",
     *       "created_at": "2025-11-15 10:30:00",
     *       "updated_at": "2025-11-15 10:30:00"
     *     }
     *   ]
     * }
     * 
     * @response 422 {
     *   "ok": false,
     *   "error": "Validation Error",
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "page": ["The page must be at least 1."],
     *     "limit": ["The limit must be at least 10."]
     *   }
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
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

            $data = CategoryType::get();

            return $this->successResponse($data, 'Tipos de categoría obtenidos exitosamente');

            //! Si quieren paginarse los datos, descomentar el siguiente bloque:
            //$limit = $request->query('limit', 10);
            // $categoryTypes = CategoryType::paginate($limit);

            // $data = [
            //     'category_types' => $categoryTypes->items(),
            //     'pagination' => [
            //         'current_page' => $categoryTypes->currentPage(),
            //         'last_page' => $categoryTypes->lastPage(),
            //         'per_page' => $categoryTypes->perPage(),
            //         'total' => $categoryTypes->total(),
            //         'from' => $categoryTypes->firstItem(),
            //         'to' => $categoryTypes->lastItem(),
            //     ]
            // ];

            // return $this->successResponse($data['category_types'], 'Tipos de categoría obtenidos exitosamente', $data['pagination']);

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
