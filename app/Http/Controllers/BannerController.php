<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use DB;
use Exception;
use App\Banner;
use App\Exceptions\ServerErrorException;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\SearchFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @resource Banners
 *
 * Endpoints para la gestión de banners.
 * Usa JWT en el header: `Authorization: Bearer {token}`.
 */
class BannerController extends Controller
{
  use ApiResponse;
  use SearchFilter;

  public function __construct()
  {
    $this->middleware('auth:api')->except(['getActiveBanner', 'getLastActiveBanner']);
  }

  /**
   * Listar banners
   * 
   * Obtiene una lista paginada de banners con opciones de búsqueda y filtrado.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros de consulta (Query Parameters):**
   * - `page` (integer, opcional): Número de página. Por defecto: 1. Ejemplo: `1`
   * - `limit` (integer, opcional): Resultados por página (mínimo 1). Por defecto: 10. Ejemplo: `10`
   * - `search` (string, opcional): Búsqueda por título. Ejemplo: `Promoción`
   * - `state` (integer, opcional): Filtrar por estado (0=Inactivo, 1=Activo). Ejemplo: `1`
   * - `sort` (string, opcional): Campo para ordenar (idBanner, title, state). Ejemplo: `idBanner:desc`
   * 
   * ```bash
   * curl -X GET "http://localhost/api/banners/getBanners?page=1&limit=10&state=1" \
   *   -H "Accept: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
   * ```
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Publicaciones obtenidas correctamente",
   *   "data": [
   *     {
   *       "idBanner": 1,
   *       "title": "Promoción 2025",
    *       "resourceURL": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
   *       "state": 1,
   *       "created_at": "2025-11-15 10:30:00",
   *       "updated_at": "2025-11-15 10:30:00"
   *     }
   *   ],
   *   "pagination": {
   *     "total": 5,
   *     "current_page": 1,
   *     "last_page": 1,
   *     "per_page": 10,
   *     "from": 1,
   *     "to": 5
   *   }
   * }
   * 
   * @response 422 {
   *   "ok": false,
   *   "error": "Validation Error",
   *   "message": "Los datos proporcionados no son válidos",
   *   "errors": {
   *     "page": ["The page must be at least 1."]
   *   }
   * }
   */
  public function index(Request $request)
  {

    try {

      $validator = Validator::make($request->all(), [
        'page' => 'integer|min:1',
        'limit' => 'integer|min:1',
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $allowedSortFields = ['idBanner', 'title', 'state'];
      $limit = $request->query('limit', 10);
      $search = $request->query('search', null);
      $isActive = $request->has('state') ? (int) $request->query('state') : null;

      $query = Banner::with([]);

      if ($search) {
        $query->where(
          function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%');
          }
        );
      }

      if ($isActive !== null) {
        $query->where('state', $isActive);
      }

      if ($request->has('sort')) {
        $this->search($request, $query, $allowedSortFields);
      } else {
        // Ordenamiento por defecto
        $query->orderBy('idBanner', 'desc');
      }

      $banners = $query->paginate($limit);

      $pagination = [
        'total' => $banners->total(),
        'current_page' => $banners->currentPage(),
        'last_page' => $banners->lastPage(),
        'per_page' => $banners->perPage(),
        'from' => $banners->firstItem(),
        'to' => $banners->lastItem(),
      ];

      return $this->successResponse($banners->items(), 'Publicaciones obtenidas correctamente', $pagination, 200);

    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException('Error al obtener los banner', null, $e);
    }

  }

  /**
   * Crear un banner
   * 
   * Crea un nuevo banner en el sistema.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros del Body:**
   * - `title` (string, requerido): Título del banner (máximo 255 caracteres). Ejemplo: `Promoción 2025`
    * - `resourceURL` (string, requerido): URL única del video de YouTube, TikTok, Facebook o Instagram (máximo 255 caracteres).
   * - `state` (boolean, requerido): Estado del banner (0=Inactivo, 1=Activo). Ejemplo: `1`
   * 
   * ```json
   * {
   *   "title": "Promoción 2025",
    *   "resourceURL": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
   *   "state": 1
   * }
   * ```
   * 
   * ```bash
   * curl -X POST "http://localhost/api/banners/createBanner" \
   *   -H "Accept: application/json" \
   *   -H "Content-Type: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
    *   -d '{"title":"Video editorial","resourceURL":"https://www.youtube.com/watch?v=dQw4w9WgXcQ","state":1}'
   * ```
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Banner creado correctamente",
   *   "data": {
   *     "idBanner": 1,
   *     "title": "Promoción 2025",
    *     "resourceURL": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
   *     "state": 1,
   *     "created_at": "2025-11-15 10:30:00",
   *     "updated_at": "2025-11-15 10:30:00"
   *   }
   * }
   * 
   * @response 422 {
   *   "ok": false,
   *   "error": "Validation Error",
   *   "message": "Los datos proporcionados no son válidos",
   *   "errors": {
   *     "title": ["The title field is required."],
   *     "resourceURL": ["The resource URL field is required."],
   *     "state": ["The state field is required."]
   *   }
   * }
   * 
   * @response 401 {
   *   "message": "Unauthenticated"
   * }
   */
  public function create(Request $request)
  {
    try {

      $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'resourceURL' => [
          'required',
          'url',
          'max:255',
          'unique:banners,resourceURL',
          function ($attribute, $value, $fail) {
            if (!$this->isSupportedVideoURL($value)) {
              $fail('La URL debe pertenecer a YouTube, TikTok, Facebook o Instagram.');
            }
          },
        ],
        'state' => 'required|boolean',
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $createdBanner = DB::transaction(function () use ($request) {
        if ((int) $request->input('state') === 1) {
          Banner::where('state', 1)->update(['state' => 0]);
        }

        $banner = Banner::create([
          'title' => $request->input('title'),
          'resourceURL' => $request->input('resourceURL'),
          'state' => $request->input('state'),
        ]);
        return $banner;
      });

      return $this->successResponse($createdBanner, 'Banner creado correctamente', null, 201);

    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException('Error al crear el banner', null, $e);
    }
  }

  /**
   * Actualizar un banner
   * 
   * Actualiza la información de un banner existente.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros de ruta (URL Parameters):**
   * - `id` (integer, requerido): ID del banner a actualizar. Ejemplo: `1`
   * 
   * **Parámetros del Body (JSON o Form Data):**
   * - `title` (string, requerido): Título del banner (máximo 255 caracteres). Ejemplo: `Promoción Actualizada 2025`
    * - `resourceURL` (string, requerido): URL única del video de YouTube, TikTok, Facebook o Instagram (máximo 255 caracteres).
   * - `state` (boolean, requerido): Estado del banner (0=Inactivo, 1=Activo). Ejemplo: `0`
   * 
   * ```json
   * {
   *   "title": "Promoción Actualizada 2025",
    *   "resourceURL": "https://www.instagram.com/reel/C0UDBvideo/",
   *   "state": 0
   * }
   * ```
   * 
   * ```bash
   * curl -X POST "http://localhost/api/banners/updateBanner/1" \
   *   -H "Accept: application/json" \
   *   -H "Content-Type: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..." \
    *   -d '{"title":"Video editorial actualizado","resourceURL":"https://www.instagram.com/reel/C0UDBvideo/","state":0}'
   * ```
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Banner actualizado correctamente",
   *   "data": {
   *     "idBanner": 1,
   *     "title": "Promoción Actualizada 2025",
    *     "resourceURL": "https://www.instagram.com/reel/C0UDBvideo/",
   *     "state": 0,
   *     "created_at": "2025-11-15 10:30:00",
   *     "updated_at": "2025-11-15 11:30:00"
   *   }
   * }
   * 
   * @response 400 {
   *   "ok": false,
   *   "error": "Bad Request",
   *   "message": "El numero de ID es requerido"
   * }
   * 
   * @response 404 {
   *   "ok": false,
   *   "error": "Not Found",
   *   "message": "Banner no encontrado"
   * }
   * 
   * @response 422 {
   *   "ok": false,
   *   "error": "Validation Error",
   *   "message": "Los datos proporcionados no son válidos",
   *   "errors": {
   *     "title": ["The title field is required."],
   *     "resourceURL": ["The resource URL has already been taken."],
   *     "state": ["The state field is required."]
   *   }
   * }
   * 
   * @response 401 {
   *   "message": "Unauthenticated"
   * }
   */
  public function update(Request $request, $id = null)
  {
    try {

      if (empty($id) || !is_numeric($id)) {
        throw new BadRequestException('El numero de ID es requerido');
      }

      $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'resourceURL' => [
          'required',
          'url',
          'max:255',
          'unique:banners,resourceURL,' . $id . ',idBanner',
          function ($attribute, $value, $fail) {
            if (!$this->isSupportedVideoURL($value)) {
              $fail('La URL debe pertenecer a YouTube, TikTok, Facebook o Instagram.');
            }
          },
        ],
        'state' => 'required|boolean',
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $updatedBanner = DB::transaction(function () use ($request, $id) {
        $banner = Banner::findOrFail($id);
        if ((int) $request->input('state') === 1 || (int) $banner->state === 1) {
          Banner::where('state', 1)
            ->where('idBanner', '!=', $id)
            ->update(['state' => 0]);
        }

        $banner->title = $request->input('title');
        $banner->resourceURL = $request->input('resourceURL');
        $banner->state = $request->input('state');
        $banner->save();

        return $banner;
      });

      return $this->successResponse($updatedBanner, 'Banner actualizado correctamente', null, 200);

    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException('Error al actualizar el banner', null, $e);
    }
  }

  /**
   * Alternar estado de un banner
   * 
   * Habilita o deshabilita un banner específico.
   * 
   * **Autenticación requerida:**
   * Este endpoint requiere un token JWT válido en el header de autorización.
   * 
   * **Parámetros de ruta (URL Parameters):**
   * - `id` (integer, requerido): ID del banner a alternar. Ejemplo: `1`
   * 
   * ```bash
   * curl -X POST "http://localhost/api/banners/toggleState/1" \
   *   -H "Accept: application/json" \
   *   -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci..."
   * ```
   * 
   * @authenticated
   * 
   * @response {
   *   "ok": true,
   *   "message": "Estado del banner actualizado correctamente",
   *   "data": {
   *     "idBanner": 1,
   *     "title": "Promoción Actualizada 2025",
   *     "resourceURL": "https://example.com/banner1_mod.jpg",
   *     "state": 1,
   *     "created_at": "2025-11-15 10:30:00",
   *     "updated_at": "2025-11-15 12:30:00"
   *   }
   * }
   * 
   * @response 400 {
   *   "ok": false,
   *   "error": "Bad Request",
   *   "message": "El numero de ID es requerido"
   * }
   * 
   * @response 404 {
   *   "ok": false,
   *   "error": "Not Found",
   *   "message": "Banner no encontrado"
   * }
   * 
   * @response 401 {
   *   "message": "Unauthenticated"
   * }
   */
  public function toggleState($id = null)
  {
    try {

      if (empty($id) || !is_numeric($id)) {
        throw new BadRequestException('El numero de ID es requerido');
      }

      $banner = DB::transaction(function () use ($id) {
        $banner = Banner::findOrFail($id);

        if ($banner->state) {
          Banner::where('state', 1)
            ->where('idBanner', '!=', $id)
            ->update(['state' => 0]);
          $banner->state = 0;
        } else {
          Banner::where('state', 1)
            ->where('idBanner', '!=', $id)
            ->update(['state' => 0]);
          $banner->state = 1;
        }

        $banner->save();
        return $banner;
      });

      return $this->successResponse($banner, 'Estado del banner actualizado correctamente', null, 200);

    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException('Error al actualizar banner', null, $e);
    }
  }

  /**
   * Obtener el único video activo
   * 
   * Retorna el video que se encuentra activo. Las operaciones de escritura mantienen un único video activo.
   * 
   * **Autenticación requerida:**
   * Este endpoint NO requiere autenticación (público).
   * 
   * ```bash
   * curl -X GET "http://localhost/api/banners/getLastActiveBanner" \
   *   -H "Accept: application/json"
   * ```
   * 
   * @response {
   *   "ok": true,
   *   "message": "Operación exitosa",
   *   "data": {
   *     "idBanner": 5,
   *     "title": "Gran Promoción",
    *     "resourceURL": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
   *     "state": 1,
   *     "created_at": "2025-11-15 10:30:00",
   *     "updated_at": "2025-11-15 10:30:00"
   *   }
   * }
   */
  public function getActiveBanner()
  {
    try {
      $banner = Banner::where('state', 1)->first();
      return $this->successResponse($banner);

    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException('Error al obtener los banner', null, $e);
    }
  }

  /**
   * Compatibilidad con clientes que todavía consumen el endpoint anterior.
   */
  public function getLastActiveBanner()
  {
    return $this->getActiveBanner();
  }

  private function isSupportedVideoURL($resourceURL)
  {
    $host = strtolower(parse_url($resourceURL, PHP_URL_HOST) ?: '');
    $path = parse_url($resourceURL, PHP_URL_PATH) ?: '';
    $query = [];
    parse_str(parse_url($resourceURL, PHP_URL_QUERY) ?: '', $query);

    if ($this->isURLHost($host, 'youtube.com')) {
      return isset($query['v'])
        || preg_match('~^/[\\w-]{11}/?$~', $path)
        || preg_match('~^/(shorts|embed|live)/[\\w-]{11}~', $path);
    }

    if ($host === 'youtu.be' || substr($host, -strlen('.youtu.be')) === '.youtu.be') {
      return preg_match('~^/[\\w-]{11}/?$~', $path);
    }

    if ($this->isURLHost($host, 'tiktok.com')) {
      return preg_match('~/@[^/]+/video/\\d+~', $path);
    }

    if ($this->isURLHost($host, 'instagram.com')) {
      return preg_match('~/(reel|p|tv)/[\\w-]+~', $path);
    }

    if ($this->isURLHost($host, 'fb.watch')) {
      return true;
    }

    if ($this->isURLHost($host, 'facebook.com')) {
      return isset($query['v'])
        || preg_match('~/(videos|reel)/[^/]+~', $path)
        || preg_match('~/share/(v|r)/[^/]+~', $path);
    }

    return false;
  }

  private function isURLHost($host, $domain)
  {
    return $host === $domain || substr($host, -strlen('.' . $domain)) === '.' . $domain;
  }

}
