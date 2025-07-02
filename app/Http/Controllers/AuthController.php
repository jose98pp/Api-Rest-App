<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{   
    public function __construct()
    {
       // $this->middleware('auth:api', ['except' => ['register', 'login']]);

    }
    /**
     * Register a new user and generate a JWT token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
            if (!$token = JWTAuth::attempt($credentials)) {
        return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }

    /**
     * Respond with the JWT token.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    
     public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:user,admin',
            'birth_date' => 'nullable|date',
            'phone' => 'nullable|string|max:15',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'role' => $request->role,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'birth_date' => $request->birth_date,
            'phone' => $request->phone,
            'image' => $request->image,
        ]);

        // Genera token automático al registrarse
        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
    }

   

    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }
    /**
     * Respond with the JWT token.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        $token = JWTAuth::getToken();
        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }
        try {
            $newToken = JWTAuth::refresh($token);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token refresh failed'], 401);
        }
        return $this->respondWithToken($newToken);
    }
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60, // Convertir a segundos
        ]);
    }

    public function getUser()
    {
        return response()->json(JWTAuth::user());
    }
}
