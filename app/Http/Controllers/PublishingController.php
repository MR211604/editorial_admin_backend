<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Http\Traits\SearchFilter;
use DB;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerErrorException;
use Illuminate\Http\Request;
use App\Publishing;
use Illuminate\Support\Facades\Validator;

class PublishingController extends Controller
{

    use ApiResponse;
    use SearchFilter;
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    private function deleteCoverImage($coverPath)
    {
        try {
            if ($coverPath) {
                $fullPath = public_path($coverPath);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
        } catch (Exception $e) {
            // Log del error pero no detener la ejecución
            \Log::warning("No se pudo eliminar la imagen: {$coverPath}");
        }
    }

    private function sanitizeFileName($name)
    {
        // Convertir a minúsculas
        $name = mb_strtolower($name, 'UTF-8');

        // Reemplazar caracteres especiales
        $name = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', ' ', '/', '\\', '&'],
            ['a', 'e', 'i', 'o', 'u', 'n', '_', '_', '_', 'y'],
            $name
        );

        // Eliminar caracteres no permitidos
        $name = preg_replace('/[^a-z0-9_-]/', '', $name);

        // Limitar longitud
        return substr($name, 0, 50);
    }

    private function storeCoverImage($file, $publishingType, $categoryId, $title)
    {
        try {
            // Determinar tipo de publicación
            $typeFolder = $publishingType == 1 ? 'libros' : 'revistas';

            // Obtener nombre de categoría
            $categoryFolder = 'sin_categoria';
            if ($categoryId) {
                $category = \App\Category::find($categoryId);
                if ($category) {
                    $categoryFolder = $this->sanitizeFileName($category->title);
                }
            }

            // Sanitizar nombre de la publicación
            $titleFolder = $this->sanitizeFileName($title);

            // Generar nombre único para el archivo
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . uniqid() . '.' . $extension;

            // Ruta completa: editorial_files/libros|revistas/categoria/titulo/imagen.jpg
            $relativePath = "editorial_files/{$typeFolder}/{$categoryFolder}";
            $fullPath = public_path($relativePath);

            // Crear directorio si no existe
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            // Mover archivo
            $file->move($fullPath, $fileName);

            // Retornar ruta relativa para guardar en BD
            return "{$relativePath}/{$fileName}";

        } catch (Exception $e) {
            throw new ServerErrorException('Error al almacenar la imagen', null, $e);
        }
    }

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'limit' => 'integer|min:10',
                'publishing_type' => 'integer'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $allowedSortFields = ['id', 'title', 'created_at', 'updated_at', 'publishing_type'];
            // $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            $publishingType = $request->query('publishing_type', null);
            $search = $request->query('search', null);
            $category = $request->query('category', null);
            $isArchived = $request->has('is_archived') ? (int) $request->query('is_archived') : null;

            $query = Publishing::with(['type', 'authors', 'details']);

            if ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            }

            if ($isArchived !== null) {
                $query->where('is_archived', $isArchived);
            }

            if ($category) {
                $query->where('category_id', $category);
            }

            if ($publishingType) {
                $query->where('publishing_type', $publishingType);
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

        } catch (ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener las publicaciones', null, $e);
        }
    }

    public function find($id = null)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El numero de ID es requerido');
            }

            if (!Publishing::where('id', $id)->exists()) {
                throw new NotFoundException('Publicación no encontrada');
            }

            $publishing = Publishing::with(['type', 'authors', 'details'])->findOrFail($id);

            return $this->successResponse($publishing, 'Publicación obtenida exitosamente');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new NotFoundException('Publicación no encontrada');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            // Dejar que las excepciones de API lleguen al Handler
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener la publicación', null, $e);
        }
    }

    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'URL' => 'nullable|string',
                'category_id' => 'nullable|integer|exists:categories,id',
                'publishing_type' => 'required|integer|exists:publishing_types,id',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $publishing = Publishing::create([
                'title' => $request->input('title'),
                'URL' => $request->input('URL'),
                'category_id' => $request->input('category_id'),
                'cover' => null,
                'age' => null,
                'volume' => null,
                'volume_date' => null,
                'publish_code' => null,
                'is_new_arrival' => 0,
                'publish_date' => null,
                'publisher' => null,
                'stock' => null,
                'author_id' => null,
                'language_id' => null,
                'publishing_type' => $request->input('publishing_type'),
                'age_number' => null,
                'pages' => null,
            ]);

            return $this->successResponse($publishing, 'Publicacion creada exitosamente', null, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            // Dejar que las excepciones de API lleguen al Handler
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al crear la publicación', null, $e);
        }
    }

    public function update(Request $request, $id = null)
    {

        try {

            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El numero de ID es requerido');
            }

            $updatedPublishing = Publishing::with(['authors', 'category', 'type', 'details'])->findOrFail($id);

            //! Operacion para los libros
            if ($request->has('publishing_type') && $request->input('publishing_type') == 1) {
                if (!Publishing::where('id', $id)->where('publishing_type', 1)->exists()) {
                    throw new NotFoundException('Publicación no encontrada');
                }

                $validator = Validator::make($request->all(), [
                    'publishing_type' => 'required|integer|exists:publishing_types,id',
                    'title' => 'required|string|max:255',
                    'pages' => 'required|integer|min:1',
                    'publish_date' => 'required|date',
                    'publish_code' => [
                        'required',
                        'string',
                        'regex:/^\d{3}-\d{2}-\d{4}-\d{3}-\d$/'
                    ],
                    'category_id' => 'nullable|exists:categories,id',
                    'is_new_arrival' => 'boolean',
                    'URL' => 'nullable|string',
                    'authors' => 'array',
                    'authors.*' => 'integer|exists:authors,id',
                    'cover' => 'nullable',
                ]);

                if ($validator->fails()) {
                    throw new ValidationException($validator);
                }

                DB::transaction(function () use ($id, $request) {
                    // Buscar la publicación con algunas relaciones
                    $publishing = Publishing::with(['category', 'type', 'details'])->findOrFail($id);

                    if ($request->hasFile('cover')) {
                        // Eliminar imagen anterior si existe
                        if ($publishing->cover) {
                            $this->deleteCoverImage($publishing->cover);
                        }
                        $coverPath = $this->storeCoverImage(
                            $request->file('cover'),
                            $request->input('publishing_type'),
                            $request->input('category_id'),
                            $request->input('title')
                        );

                        $publishing->cover = $coverPath;
                    }

                    // Actualizar atributos principales
                    $publishing->update($request->only([
                        'publishing_type',
                        'title',
                        'pages',
                        'publish_date',
                        'publish_code',
                        'category_id',
                        'is_new_arrival',
                        'URL'
                    ]));

                    // Sincronizar autores
                    $publishing->authors()->sync($request->input('authors'));
                });

            }

            //? Operacion para las revistas
            if ($request->has('publishing_type') && $request->input('publishing_type') == 2) {

                //Esta validacion asegura que la publicación sea del tipo revista
                if (!Publishing::where('id', $id)->where('publishing_type', 2)->exists()) {
                    throw new NotFoundException('Publicación no encontrada');
                }

                $validator = Validator::make($request->all(), [
                    'title' => 'required|string|max:255',
                    'pages' => 'required|integer|min:1',
                    'publish_date' => 'required|date',
                    'publish_code' => [
                        'required',
                        'string',
                        'regex:/^\d{3}-\d{2}-\d{4}-\d{3}-\d$/'
                    ],
                    'age' => 'nullable|integer|min:1',
                    'age_number' => 'nullable|integer|min:1',
                    'volume' => 'nullable|integer|min:1',
                    'is_new_arrival' => 'boolean',
                    'category_id' => 'nullable|exists:categories,id',
                    'publishing_type' => 'required|integer|exists:publishing_types,id',
                    'authors' => 'array',
                    'authors.*' => 'integer|exists:authors,id',
                    'cover' => 'nullable',
                ]);

                if ($validator->fails()) {
                    throw new ValidationException($validator);
                }

                // Usar transacción para asegurar integridad de datos
                DB::transaction(function () use ($id, $request) {
                    // Buscar la publicación con algunas relaciones
                    $publishing = Publishing::with(['category', 'type', 'details'])->findOrFail($id);

                    if ($request->hasFile('cover')) {
                        // Eliminar imagen anterior si existe
                        if ($publishing->cover) {
                            $this->deleteCoverImage($publishing->cover);
                        }

                        $coverPath = $this->storeCoverImage(
                            $request->file('cover'),
                            $request->input('publishing_type'),
                            $request->input('category_id'),
                            $request->input('title')
                        );

                        $publishing->cover = $coverPath;
                    }

                    // Actualizar atributos principales
                    $publishing->update($request->only([
                        'title',
                        'publish_date',
                        'publish_code',
                        'age',
                        'age_number',
                        'pages',
                        'volume',
                        'is_new_arrival',
                        'category_id',
                        'publishing_type'
                    ]));

                    // Sincronizar autores
                    $publishing->authors()->sync($request->input('authors'));
                });
            }

            return $this->successResponse($updatedPublishing, 'Publicación actualizada exitosamente', null, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new NotFoundException('Publicación no encontrada');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\App\Exceptions\ApiException $e) {
            // Dejar que las excepciones de API lleguen al Handler
            throw $e;
        } catch (\Exception $e) {
            throw new ServerErrorException('Error al actualizar la publicación', null, $e);
        }
    }

    //Actualizando el estado de la publicacion 
    // 1 - Archivado
    // 0 - No archivado
    public function toggleStatus(Request $request, $id = null)
    {
        try {

            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El ID de publicación es requerido');
            }

            $validator = Validator::make($request->all(), [
                'is_archived' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $publishing = Publishing::findOrFail($id);
            $updatedStatus = $request->input('is_archived');
            $publishing->update(['is_archived' => $updatedStatus]);

            $message = $updatedStatus ? 'Publicación archivada exitosamente' : 'Publicación reactivada exitosamente';

            return $this->successResponse(null, $message, null, 200);

        } catch (Exception $e) {
            throw new ServerErrorException('Error al actualizar el estado de la publicación', null, $e);
        }
    }

    public function summary()
    {
        try {
            $booksCount = Publishing::where('publishing_type', 1)->count();
            $magazinesCount = Publishing::where('publishing_type', 2)->count();

            $data = [
                'books' => $booksCount,
                'magazines' => $magazinesCount,
            ];

            return $this->successResponse($data, 'Resumen del dashboard obtenido correctamente', null, 200);
        } catch (Exception $e) {
            throw new ServerErrorException('Error al obtener el resumen del dashboard', null, $e);
        }
    }

    public function delete($id = null)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new BadRequestException('El ID de publicación es requerido');
            }

            $publishing = Publishing::findOrFail($id);

            //Si el campo is_archived es 0, no se puede eliminar
            if ($publishing->is_archived == 0) {
                throw new BadRequestException('La publicación debe estar archivada para poder eliminarla');
            }

            DB::transaction(function () use ($publishing) {

                if ($publishing->cover) {
                    $this->deleteCoverImage($publishing->cover);
                }

                $publishing->authors()->detach();
                $publishing->details()->delete();
                $publishing->delete();
            });

            return $this->successResponse(null, 'Publicación eliminada exitosamente', null, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new NotFoundException('Publicación no encontrada');
        } catch (\App\Exceptions\ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerErrorException('Error al eliminar la publicación', null, $e);
        }
    }

}
