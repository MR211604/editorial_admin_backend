<?php

namespace App\Http\Controllers;

use App\EditorialLocation;
use App\Exceptions\BadRequestException;
use App\Exceptions\ServerErrorException;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EditorialLocationController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api')->except(['active']);
    }

    public function index()
    {
        try {
            $locations = EditorialLocation::orderBy('sort_order')
                ->orderBy('idLocation')
                ->get();

            return $this->successResponse($locations, 'Ubicaciones obtenidas correctamente');
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener las ubicaciones', null, $e);
        }
    }

    public function active()
    {
        try {
            $locations = EditorialLocation::where('state', 1)
                ->orderBy('sort_order')
                ->orderBy('idLocation')
                ->get(['idLocation', 'name', 'image', 'address', 'mapsQuery', 'sort_order']);

            return $this->successResponse($locations, 'Ubicaciones activas obtenidas correctamente');
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener las ubicaciones activas', null, $e);
        }
    }

    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'image' => 'required|string|max:2048',
                'address' => 'required|string|max:500',
                'mapsQuery' => 'required|string|max:500',
                'state' => 'required|boolean',
                'sort_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $location = EditorialLocation::create($request->only([
                'name', 'image', 'address', 'mapsQuery', 'state', 'sort_order',
            ]));

            return $this->successResponse($location, 'Ubicación creada correctamente', null, 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al crear la ubicación', null, $e);
        }
    }

    public function update(Request $request, $id = null)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El número de ID es requerido');
            }

            $location = EditorialLocation::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'image' => 'required|string|max:2048',
                'address' => 'required|string|max:500',
                'mapsQuery' => 'required|string|max:500',
                'state' => 'required|boolean',
                'sort_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $location->update($request->only([
                'name', 'image', 'address', 'mapsQuery', 'state', 'sort_order',
            ]));

            return $this->successResponse($location->fresh(), 'Ubicación actualizada correctamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new \App\Exceptions\NotFoundException('Ubicación no encontrada');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al actualizar la ubicación', null, $e);
        }
    }

    public function toggleState($id = null)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El número de ID es requerido');
            }

            $location = EditorialLocation::findOrFail($id);
            $location->state = !$location->state;
            $location->save();

            $message = $location->state
                ? 'Ubicación activada correctamente'
                : 'Ubicación ocultada correctamente';

            return $this->successResponse($location, $message);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new \App\Exceptions\NotFoundException('Ubicación no encontrada');
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al cambiar el estado de la ubicación', null, $e);
        }
    }

    public function uploadImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
                'name' => 'string|nullable|max:255',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $file = $request->file('file');
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $request->input('name', 'ubicacion'));
            $fileName = $safeName . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $relativePath = 'imageslocations';
            $fullPath = public_path($relativePath);

            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            $file->move($fullPath, $fileName);

            return $this->successResponse([
                'path' => $relativePath . '/' . $fileName,
                'url' => url($relativePath . '/' . $fileName),
            ], 'Logo subido correctamente');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al almacenar el logo de la ubicación', null, $e);
        }
    }
}
