<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpWhitelist;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IpWhitelistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ipWhitelists = IpWhitelist::orderBy('category')->orderBy('ip_address')->paginate(15);
        return view('admin.ip-whitelist.index', compact('ipWhitelists'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.ip-whitelist.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip|unique:ip_whitelists,ip_address,NULL,id,category,' . $request->category,
            'description' => 'nullable|string|max:255',
            'category' => 'required|in:admin,operator,general',
            'is_active' => 'boolean',
            'expires_at' => 'nullable|date|after:today',
        ]);

        IpWhitelist::create([
            'ip_address' => $request->ip_address,
            'description' => $request->description,
            'category' => $request->category,
            'is_active' => $request->has('is_active'),
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('admin.ip-whitelist.index')
                         ->with('success', 'IP address added to whitelist successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ipWhitelist = IpWhitelist::findOrFail($id);
        return view('admin.ip-whitelist.show', compact('ipWhitelist'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $ipWhitelist = IpWhitelist::findOrFail($id);
        return view('admin.ip-whitelist.edit', compact('ipWhitelist'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ipWhitelist = IpWhitelist::findOrFail($id);

        $request->validate([
            'ip_address' => [
                'required',
                'ip',
                Rule::unique('ip_whitelists')->ignore($id)->where(function ($query) use ($request) {
                    return $query->where('category', $request->category);
                }),
            ],
            'description' => 'nullable|string|max:255',
            'category' => 'required|in:admin,operator,general',
            'is_active' => 'boolean',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $ipWhitelist->update([
            'ip_address' => $request->ip_address,
            'description' => $request->description,
            'category' => $request->category,
            'is_active' => $request->has('is_active'),
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('admin.ip-whitelist.index')
                         ->with('success', 'IP whitelist entry updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ipWhitelist = IpWhitelist::findOrFail($id);
        $ipWhitelist->delete();

        return redirect()->route('admin.ip-whitelist.index')
                         ->with('success', 'IP address removed from whitelist successfully.');
    }

    /**
     * Toggle the active status of an IP whitelist entry
     */
    public function toggleStatus(string $id)
    {
        $ipWhitelist = IpWhitelist::findOrFail($id);
        $ipWhitelist->update(['is_active' => !$ipWhitelist->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'IP status updated successfully',
            'is_active' => $ipWhitelist->fresh()->is_active
        ]);
    }
}
