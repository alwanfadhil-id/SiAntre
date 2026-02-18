<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class DisplaySelectorController extends Controller
{
    /**
     * Display selection page - choose which services to show
     */
    public function select()
    {
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('display.select', compact('services'));
    }

    /**
     * Show the display with selected services
     */
    public function show(Request $request)
    {
        $selectedServices = $request->input('services', []);
        $displayMode = $request->input('mode', 'grid'); // grid, list, single

        // If no services selected, redirect to selection page
        if (empty($selectedServices)) {
            return redirect()->route('display.select');
        }

        // Get selected services details
        $services = Service::whereIn('id', $selectedServices)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // If only 1 service, use single mode
        if ($services->count() === 1) {
            $displayMode = 'single';
        }

        return view('display.show', compact('services', 'displayMode', 'selectedServices'));
    }
}
