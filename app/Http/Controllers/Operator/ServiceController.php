<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    public function index()
    {
        try {
            // Cache services for 1 hour
            $services = cache()->remember('operator_services_list', 3600, function () {
                return Service::all();
            });

            return view('operator.services', compact('services'));
        } catch (\Exception $e) {
            Log::error('Error loading operator services: ' . $e->getMessage());

            // Return with default values in case of error
            return view('operator.services', [
                'services' => collect([])
            ])->withErrors(['error' => 'Terjadi kesalahan saat memuat daftar layanan.']);
        }
    }
}
