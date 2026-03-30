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

class BannerController extends Controller
{
  use ApiResponse;
  use SearchFilter;

  public function __construct()
  {
    $this->middleware('auth:api')->except(['getLastActiveBanner']);
  }


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

  public function create(Request $request)
  {
    try {

      $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'resourceURL' => 'required|url|unique:banners,resourceURL',
        'state' => 'required|boolean',
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $createdBanner = DB::transaction(function () use ($request) {
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

  public function update(Request $request, $id = null)
  {
    try {

      if (empty($id) || !is_numeric($id)) {
        throw new BadRequestException('El numero de ID es requerido');
      }

      $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'resourceURL' => 'required|url|unique:banners,resourceURL,' . $id . ',idBanner',
        'state' => 'required|boolean',
      ]);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $updatedBanner = DB::transaction(function () use ($request, $id) {
        $banner = Banner::findOrFail($id);
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

  public function toggleState($id = null)
  {
    try {

      if (empty($id) || !is_numeric($id)) {
        throw new BadRequestException('El numero de ID es requerido');
      }

      $banner = Banner::findOrFail($id);
      $banner->state = !$banner->state;
      $banner->save();

      return $this->successResponse($banner, 'Estado del banner actualizado correctamente', null, 200);

    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException($e->getMessage(), null, $e);
    }
  }

  // Obtener el ultimo banner activo
  public function getLastActiveBanner()
  {
    try {

      // Obteniendo el ultimo banner activo
      $banner = Banner::where('state', 1)->orderBy('idBanner', 'desc')->first();

      return $this->successResponse($banner);

    } catch (ValidationException $e) {
      throw $e;
    } catch (\App\Exceptions\ApiException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new ServerErrorException('Error al obtener los banner', null, $e);
    }
  }

}