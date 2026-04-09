<?php

namespace App\Http\Controllers;

use App\Services\GarcomService;

class GarcomController extends Controller
{
    public function __construct(private GarcomService $garcomService)
    {
    }

    public function index()
    {
        return view('Admin.Garcom');
    }
}
