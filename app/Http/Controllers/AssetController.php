<?php
namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // need to change this to fill the defect and accesory table
        $request->validate([
            'code'          => 'required|unique:assets,code',
            'name'          => 'required|string',
            'asset_type_id' => 'required|exists:asset_types,id',
            'brand'         => 'nullable|string',
            'model'         => 'nullable|string',
            'color'         => 'nullable|string',
            'status'        => 'required|string|in:IN_USE,AVAILABLE,DAMAGED,REPAIRING,PENDING_DISPOSAL,DISPOSED,LOST,BORROWED,PENDING_INSPECTION,DISABLED',
        ]);

        return Asset::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
