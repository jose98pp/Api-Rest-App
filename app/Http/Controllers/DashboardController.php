<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $data = Dashboard::getDashboardData();
            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los datos del dashboard: ' . $e->getMessage()
            ], 500);
        }
    }
}