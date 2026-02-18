<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::all();
        return view('admin.services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.services.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'prefix' => 'required|string|max:10|unique:services,prefix|regex:/^[A-Za-z0-9]+$/',
        ]);

        $service = Service::create([
            'name' => trim($request->name),
            'prefix' => strtoupper(trim($request->prefix)),
        ]);

        // Clear the services list cache to ensure changes appear immediately
        cache()->forget('services_list');
        cache()->forget('operator_services_list');

        // Log the service creation
        $monitoringService = new \App\Services\QueueMonitoringService();
        $monitoringService->logServiceManagement(auth()->id(), 'create', $service->id, [
            'name' => $service->name,
            'prefix' => $service->prefix
        ]);

        return redirect()->route('admin.services.index')
                         ->with('success', 'Layanan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        return view('admin.services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name,'.$service->id,
            'prefix' => 'required|string|max:10|unique:services,prefix,'.$service->id.'|regex:/^[A-Za-z0-9]+$/',
        ]);

        $oldData = ['name' => $service->name, 'prefix' => $service->prefix];

        $service->update([
            'name' => trim($request->name),
            'prefix' => strtoupper(trim($request->prefix)),
        ]);

        // Clear the services list cache to ensure changes appear immediately
        cache()->forget('services_list');
        cache()->forget('operator_services_list');

        // Log the service update
        $monitoringService = new \App\Services\QueueMonitoringService();
        $monitoringService->logServiceManagement(auth()->id(), 'update', $service->id, [
            'old' => $oldData,
            'new' => ['name' => $service->name, 'prefix' => $service->prefix]
        ]);

        return redirect()->route('admin.services.index')
                         ->with('success', 'Layanan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        // Check if there are any queues associated with this service
        if ($service->queues()->count() > 0) {
            return redirect()->route('admin.services.index')
                             ->with('error', 'Tidak dapat menghapus layanan yang sudah memiliki antrian.');
        }

        $serviceData = ['name' => $service->name, 'prefix' => $service->prefix];

        $service->delete();

        // Clear the services list cache to ensure changes appear immediately
        cache()->forget('services_list');
        cache()->forget('operator_services_list');

        // Log the service deletion
        $monitoringService = new \App\Services\QueueMonitoringService();
        $monitoringService->logServiceManagement(auth()->id(), 'delete', $service->id, $serviceData);

        return redirect()->route('admin.services.index')
                         ->with('success', 'Layanan berhasil dihapus.');
    }
}
