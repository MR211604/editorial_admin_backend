<?php

namespace App\Http\Controllers;
use App\Exceptions\ServerErrorException;
use App\Http\Traits\ApiResponse;
use App\Mail\ContactReceived;
use App\UserEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @resource Emails
 *
 * Endpoints para la gestión de correos y mensajes de contacto recibidos.
 * Usa JWT en el header: `Authorization: Bearer {token}` (solo para listado).
 */
class EmailController extends Controller
{
  use ApiResponse;

  /**
   * Constructor (middleware)
   *
   * Aplica el middleware `auth:api` a todas las rutas.
   */
  public function __construct()
  {
    $this->middleware('throttle:5,1')->only('saveEmail');
  }

  /**
   * Listar correos
   * 
   * Obtiene una lista paginada de correos y mensajes de contacto con opciones de búsqueda y ordenamiento.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros de consulta (Query Parameters):**
   * - `page` (integer, opcional): Número de página. Por defecto: 1. Ejemplo: `1`
   * - `limit` (integer, opcional): Resultados por página (mínimo 10). Por defecto: 10. Ejemplo: `10`
   * - `search` (string, opcional): Búsqueda por nombre, correo electrónico o asunto. Ejemplo: `Consulta`
   * - `sort` (string, opcional): Campo para ordenar (idUserEmail, name, email, subject, message). Ejemplo: `idUserEmail:desc`
   * 
   * ```bash
   * curl -X GET "http://localhost/api/emails/getEmails?page=1&limit=10&search=Consulta" \
   *   -H "Accept: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
   * ```
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Correos obtenidos exitosamente",
   *   "data": [
   *     {
   *       "idUserEmail": 1,
   *       "name": "Juan Pérez",
   *       "subject": "Consulta sobre publicaciones",
   *       "email": "juan.perez@example.com",
   *       "message": "Me gustaría saber más sobre los procesos de publicación.",
   *       "created_at": "2025-11-15 10:30:00",
   *       "updated_at": "2025-11-15 10:30:00"
   *     }
   *   ],
   *   "pagination": {
   *     "total": 50,
   *     "current_page": 1,
   *     "last_page": 5,
   *     "per_page": 10,
   *     "from": 1,
   *     "to": 10
   *   }
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
        'limit' => 'integer|min:10',
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $limit = $request->query('limit', 10);
      $search = $request->query('search', null);

      $query = UserEmail::with([]);

      if ($search) {
        $query->where(function ($q) use ($search) {
          $q->where('name', 'like', '%' . $search . '%')
            ->orWhere('email', 'like', '%' . $search . '%')
            ->orWhere('subject', 'like', '%' . $search . '%');
        });
      }

      $allowedSortFields = ['idUserEmail', 'name', 'email', 'subject', 'message'];

      if ($request->has('sort')) {
        $this->search($request, $query, $allowedSortFields);
      } else {
        $query->orderBy('idUserEmail', 'desc');
      }

      $users = $query->paginate($limit);

      $pagination = [
        'total' => $users->total(),
        'current_page' => $users->currentPage(),
        'last_page' => $users->lastPage(),
        'per_page' => $users->perPage(),
        'from' => $users->firstItem(),
        'to' => $users->lastItem()
      ];

      return $this->successResponse(
        $users->items(),
        'Correos obtenidos exitosamente',
        $pagination
      );

    } catch (ValidationException $e) {
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al obtener los correos', null, $e);
    }
  }


  /**
   * Guardar correo
   * 
   * Guarda un nuevo mensaje de contacto enviado desde el formulario público. 
   * Este endpoint tiene un límite de peticiones (throttle: 5 peticiones por minuto por IP) para evitar spam.
   * 
   * **Autenticación requerida:**
   * Este endpoint NO requiere autenticación (público).
   * 
   * **Parámetros del Body:**
   * - `name` (string, requerido): Nombre del remitente (máximo 255 caracteres). Ejemplo: `Carlos López`
   * - `subject` (string, requerido): Asunto del mensaje (máximo 255 caracteres). Ejemplo: `Duda sobre compra`
   * - `email` (string, requerido): Dirección de correo válida (máximo 255 caracteres). Ejemplo: `carlos@example.com`
   * - `message` (string, opcional): Cuerpo del mensaje. Ejemplo: `Quiero comprar un libro pero tengo problemas con mi tarjeta...`
   * 
   * ```json
   * {
   *   "name": "Carlos López",
   *   "subject": "Duda sobre compra",
   *   "email": "carlos@example.com",
   *   "message": "Quiero comprar un libro pero tengo problemas con mi tarjeta..."
   * }
   * ```
   * 
   * ```bash
   * curl -X POST "http://localhost/api/emails/saveEmail" \
   *   -H "Accept: application/json" \
   *   -H "Content-Type: application/json" \
   *   -d '{"name":"Carlos López","subject":"Duda sobre compra","email":"carlos@example.com","message":"Texto del mensaje"}'
   * ```
   * 
   * @response 201 {
   *   "ok": true,
   *   "message": "Correo guardado exitosamente",
   *   "data": {
   *     "idUserEmail": 2,
   *     "name": "Carlos López",
   *     "subject": "Duda sobre compra",
   *     "email": "carlos@example.com",
   *     "message": "Quiero comprar un libro pero tengo problemas con mi tarjeta...",
   *     "created_at": "2025-11-15 11:45:00",
   *     "updated_at": "2025-11-15 11:45:00"
   *   }
   * }
   * 
   * @response 422 {
   *   "ok": false,
   *   "error": "Validation Error",
   *   "message": "Los datos proporcionados no son válidos",
   *   "errors": {
   *     "name": ["The name field is required."],
   *     "email": ["The email must be a valid email address."]
   *   }
   * }
   * 
   * @response 429 {
   *   "message": "Too Many Attempts."
   * }
   */
  public function saveEmail(Request $request)
  {

    try {
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'subject' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'message' => 'nullable|string',
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $createdUserEmail = UserEmail::create([
        'name' => $request->input('name'),
        'subject' => $request->input('subject'),
        'email' => $request->input('email'),
        'message' => $request->input('message'),
      ]);

      Mail::to(env('MAIL_FROM_ADDRESS'))->send(new ContactReceived($createdUserEmail));

      return $this->successResponse($createdUserEmail, 'Correo guardado y enviado exitosamente', null, 201);
    } catch (ValidationException $e) {
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al crear el correo', null, $e);
    }
  }
}
