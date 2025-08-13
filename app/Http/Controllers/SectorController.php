<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SectorController extends Controller
{
    /**
     * Display a listing of all non-deleted sectors.
     */
    public function index()
    {
        return Sector::all(); // Only returns non-deleted by default
    }

    /**
     * Display a listing of ALL sectors, including soft-deleted.
     */
    public function allWithTrashed()
    {
        return Sector::withTrashed()->get();
    }

    /**
     * Store a newly created sector.
     */
    public function store(Request $request)
    {
        $request->validate([
          'sector_name' => 'required|string|max:255',

        ]);

        $sector = Sector::create([
            'sector_name' => $request->sector_name,
            'created_by' => Auth::id(),
            'status' => 'active'
        ]);

        return response()->json($sector, 201);
    }

    /**
     * Check if a sector name already exists (case insensitive).
     */
   public function checkName(Request $request)
{
    $name = strtolower($request->query('name'));

    $sector = Sector::withTrashed()
        ->whereRaw('LOWER(sector_name) = ?', [$name])
        ->first();

    if (!$sector) {
        return response()->json([
            'exists' => false,
            'deleted_at' => true
        ]);
    }

    return response()->json([
        'exists' => true,
        'deleted_at' => $sector->trashed()
    ]);
}


    /**
     * Display the specified sector.
     */
    public function show($id)
    {
        $sector = Sector::withTrashed()->findOrFail($id); // Include deleted records
        return response()->json($sector);
    }

    /**
     * Update the specified sector.
     */
    public function update(Request $request, Sector $sector)
    {
        $request->validate([
            'sector_name' => 'sometimes|string|max:255|unique:sector,sector_name,' . $sector->id,
            'status' => 'in:active,inactive',
        ]);

        $sector->update($request->only(['sector_name', 'status']));

        return response()->json($sector);
    }

    /**
     * Soft delete the specified sector.
     */
   public function destroy($id)
{
    $sector = Sector::findOrFail($id);

    // Update status to inactive
    $sector->status = 'inactive';
    $sector->save();

    // Then soft delete
    $sector->delete();

    return response()->json(['message' => 'Sector deleted successfully and marked as inactive.']);
}


    /**
     * Restore a soft-deleted sector.
     */
    public function restore($id)
    {
        $sector = Sector::onlyTrashed()->findOrFail($id);
        $sector->restore();

        return response()->json(['message' => 'Sector restored successfully']);
    }

    /**
     * Permanently delete a soft-deleted sector.
     */
    public function forceDelete($id)
    {
        $sector = Sector::onlyTrashed()->findOrFail($id);
        $sector->forceDelete();

        return response()->json(['message' => 'Sector permanently deleted']);
    }
}
