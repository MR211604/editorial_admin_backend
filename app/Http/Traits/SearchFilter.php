<?php

namespace App\Http\Traits;


trait SearchFilter
{

  public function search($request, $query, $allowedSortFields)
  {
    $sorts = explode(',', $request->input('sort'));
    $directions = $request->has('direction')
      ? explode(',', $request->input('direction'))
      : [];
    foreach ($sorts as $index => $sortField) {
      if (!in_array($sortField, $allowedSortFields)) {
        return response()->json([
          'success' => false,
          'message' => "Campo de ordenamiento no permitido: {$sortField}",
          'allowed_fields' => $allowedSortFields
        ], 422);
      }

      $direction = isset($directions[$index])
        ? (strtolower($directions[$index]) === 'desc' ? 'desc' : 'asc')
        : 'asc';

      $query->orderBy($sortField, $direction);
    }
  }
}