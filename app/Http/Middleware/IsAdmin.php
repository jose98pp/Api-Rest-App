<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica si el usuario esta autenticado en la api y tiene el role de admin
        // Si lo es, permite que la peticion siga su curso
        // Si no lo es, devuelve un error 401 con un mensaje de unauthorized
        if(auth('api')->user()->role == 'admin') {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized, you are not an admin'], 401);
    }
}
