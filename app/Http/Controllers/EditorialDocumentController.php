<?php

namespace App\Http\Controllers;

use App\EditorialDocument;
use App\Exceptions\BadRequestException;
use App\Exceptions\ServerErrorException;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EditorialDocumentController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api')->except(['active', 'findBySlug']);
    }

    public function index()
    {
        try {
            $documents = EditorialDocument::orderBy('sort_order')
                ->orderBy('idDocument')
                ->get();

            return $this->successResponse($documents, 'Documentos obtenidos correctamente');
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener los documentos', null, $e);
        }
    }

    public function active()
    {
        try {
            $documents = EditorialDocument::where('state', 1)
                ->orderBy('sort_order')
                ->orderBy('idDocument')
                ->get(['idDocument', 'title', 'slug', 'content', 'sort_order']);

            return $this->successResponse($documents, 'Documentos activos obtenidos correctamente');
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener los documentos activos', null, $e);
        }
    }

    public function find($id = null)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El número de ID es requerido');
            }

            $document = EditorialDocument::findOrFail($id);

            return $this->successResponse($document, 'Documento obtenido correctamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new \App\Exceptions\NotFoundException('Documento no encontrado');
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener el documento', null, $e);
        }
    }

    public function findBySlug($slug = null)
    {
        try {
            if (empty($slug)) {
                throw new BadRequestException('El slug del documento es requerido');
            }

            $document = EditorialDocument::where('slug', $slug)
                ->where('state', 1)
                ->first();

            if (!$document) {
                throw new \App\Exceptions\NotFoundException('Documento no encontrado');
            }

            return $this->successResponse($document, 'Documento obtenido correctamente');
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al buscar el documento', null, $e);
        }
    }

    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'slug' => 'required|string|max:191|alpha_dash|unique:editorial_documents,slug',
                'content' => 'string|nullable',
                'state' => 'boolean',
                'sort_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $document = EditorialDocument::create([
                'title' => $request->input('title'),
                'slug' => $request->input('slug'),
                'content' => $request->input('content', ''),
                'state' => $request->input('state', 1),
                'sort_order' => $request->input('sort_order', 0),
            ]);

            return $this->successResponse($document, 'Documento creado correctamente', null, 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al crear el documento', null, $e);
        }
    }

    public function update(Request $request, $id = null)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El número de ID es requerido');
            }

            $document = EditorialDocument::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'slug' => 'required|string|max:191|alpha_dash|unique:editorial_documents,slug,' . $id . ',idDocument',
                'content' => 'string|nullable',
                'state' => 'required|boolean',
                'sort_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $document->update($request->only(['title', 'slug', 'content', 'state', 'sort_order']));

            return $this->successResponse($document->fresh(), 'Documento actualizado correctamente');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new \App\Exceptions\NotFoundException('Documento no encontrado');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al actualizar el documento', null, $e);
        }
    }

    public function toggleState($id = null)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El número de ID es requerido');
            }

            $document = EditorialDocument::findOrFail($id);
            $document->state = !$document->state;
            $document->save();

            $message = $document->state
                ? 'Documento activado correctamente'
                : 'Documento desactivado correctamente';

            return $this->successResponse($document, $message);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new \App\Exceptions\NotFoundException('Documento no encontrado');
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al cambiar el estado del documento', null, $e);
        }
    }

    public function uploadImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
                'title' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $file = $request->file('file');
            $safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $request->input('title'));
            $fileName = $safeTitle . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $relativePath = 'imagescontent';
            $fullPath = public_path($relativePath);

            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            $file->move($fullPath, $fileName);

            return $this->successResponse([
                'path' => $relativePath . '/' . $fileName,
                'url' => url($relativePath . '/' . $fileName),
            ], 'Imagen subida correctamente');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al almacenar la imagen del documento', null, $e);
        }
    }
}
