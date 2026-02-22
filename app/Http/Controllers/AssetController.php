<?php
namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $request->validate([
            'code'               => 'required|unique:assets,code',
            'name'               => 'required|string',
            'asset_type_id'      => 'required|exists:asset_types,id',
            'brand'              => 'nullable|string',
            'model'              => 'nullable|string',
            'color'              => 'nullable|string',
            'status'             => 'required|string|in:IN_USE,AVAILABLE,DAMAGED,REPAIRING,PENDING_DISPOSAL,DISPOSED,LOST,BORROWED,PENDING_INSPECTION,DISABLED',

            'accessories'        => 'nullable|array',
            'accessories.*.name' => 'required|string',

            'defects'            => 'nullable|array',
            'defects.*.name'     => 'required|string',
        ]);

        DB::beginTransaction();
        try {

            $asset = Asset::create([
                'code'          => $request->code,
                'name'          => $request->name,
                'asset_type_id' => $request->asset_type_id,
                'brand'         => $request->brand,
                'model'         => $request->model,
                'color'         => $request->color,
                'status'        => $request->status,
            ]);

            if ($request->has('accessories')) {
                foreach ($request->accessories as $accessory) {
                    $asset->accessories()->create([
                        'name' => $accessory['name'],
                    ]);
                }
            }

            if ($request->has('defects')) {
                foreach ($request->defects as $defect) {
                    $asset->defects()->create([
                        'name' => $defect['name'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Asset created successfully',
                'data'    => $asset->load('accessories', 'defects'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
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
