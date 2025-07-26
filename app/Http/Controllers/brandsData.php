<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\brands;

class brandsData extends Controller
{
    public function index()
    {
        // Retrieve all subCategories from the database
        $brands = brands::all();
        
        // Return the brands as JSON response
        return response()->json($brands, 200);
    }
}
