<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            return redirect()->to('/client');
        }

        return view('welcome');
    }
}
