<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    // Método para que el admin vea todos los usuarios
    public function index()
    {
        $users = User::all();
        return response()->json($users, 200);
    }
}
