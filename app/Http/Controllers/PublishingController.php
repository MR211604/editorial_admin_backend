<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Publishing;
class PublishingController extends Controller
{
    public function index() {
        // Fetch all publishings with their authors and categories
        $publishings = Publishing::with(['author', 'category'])->get();
        // Return the data as a JSON response
        return response()->json($publishings);
    }
}
