<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Service;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Cache services list for 1 hour
        $services = cache()->remember('report_services_list', 3600, function () {
            return Service::select('id', 'name')->get(); // Only select needed fields
        });

        // Build cache key based on filters
        $cacheKey = 'report_queues_';
        $cacheKey .= $request->filled('service_id') ? 's' . $request->service_id . '_' : 'all_';
        $cacheKey .= $request->filled('date') ? 'd' . $request->date . '_' : 'all_';
        $cacheKey .= $request->filled('status') ? 'st' . $request->status . '_' : 'all_';
        $cacheKey .= $request->page ?: '1';

        // Build the query with filters
        $query = Queue::with('service:id,name'); // Only load needed fields from service

        // Filter by service if provided
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by date if provided
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Order by creation date descending
        $queues = cache()->remember($cacheKey, 600, function () use ($query) { // Cache for 10 minutes
            return $query->orderBy('created_at', 'desc')->paginate(20);
        });

        return view('reports.index', compact('queues', 'services'));
    }
}
