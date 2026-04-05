<?php

namespace App\Http\Controllers;
use App\Exceptions\ServerErrorException;
use App\Http\Traits\ApiResponse;
use App\UserEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

      return $this->successResponse($createdUserEmail, 'Correo guardado exitosamente', null, 201);
    } catch (ValidationException $e) {
      throw $e;
    } catch (\Exception $e) {
      throw new ServerErrorException('Error al crear el correo', null, $e);
    }
  }
}
