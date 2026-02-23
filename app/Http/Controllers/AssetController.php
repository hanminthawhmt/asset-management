<?php
namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assets          = Asset::whereNull('deleted_at')->with(['accessories', 'defects'])->get();
        $formattedAssets = $assets->map(function ($asset) {
            return [
                'id'          => $asset->id,
                'code'        => $asset->code,
                'asset_type'  => $asset->assetType->name,
                'name'        => $asset->name,
                'brand'       => $asset->brand,
                'model'       => $asset->model,
                'color'       => $asset->color,
                'accessories' => $asset->accessories->pluck('name')->values(),
                'status'      => $asset->status,
                'defects'     => $asset->defects->pluck('name')->values(),
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $formattedAssets,
        ]);
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
    public function show(Asset $asset)
    {
        $asset->load(['assetType', 'accessories', 'defects']);
        return response()->json([
            'status' => true,
            'data'   => [
                'id'          => $asset->id,
                'code'        => $asset->code,
                'name'        => $asset->name,
                'asset_type'  => [
                    $asset->assetType->id,
                    $asset->assetType->name,
                ],
                'brand'       => $asset->brand,
                'model'       => $asset->model,
                'color'       => $asset->color,
                'status'      => $asset->status,
                'accessories' => $asset->accessories->map(function ($accessory) {
                    return [
                        'id'   => $accessory->id,
                        'name' => $accessory->name,
                    ];
                }),
                'defects'     => $asset->defects->map(function ($defect) {
                    return [
                        'id'   => $defect->id,
                        'name' => $defect->name,
                    ];
                }),
            ],
        ]);
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
    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'code'               => 'required|string',
            'asset_type_id'      => 'required|exists:asset_types,id',
            'name'               => 'required|string',
            'brand'              => 'required|string',
            'model'              => 'required|string',
            'color'              => 'required|string',
            'status'             => 'required|string|in:IN_USE,AVAILABLE,DAMAGED,REPAIRING,PENDING_DISPOSAL,DISPOSED,LOST,BORROWED,PENDING_INSPECTION,DISABLED',

            'accessories'        => 'nullable|array',
            'accessories.*.id'   => [
                'nullable',
                Rule::exists('accessories', 'id')->where(function ($query) use ($asset) {
                    $query->where('asset_id', $asset->id)->whereNull('deleted_at');
                }),
            ],
            'accessories.*.name' => 'required|string|max:255',

            'defects'            => 'nullable|array',
            'defects.*.id'       => [
                'nullable',
                Rule::exists('defects', 'id')->where(function ($query) use ($asset) {
                    $query->where('asset_id', $asset->id)->whereNull('deleted_at');
                }),
            ],
            'defects.*.name'     => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $asset) {

            $asset->update([
                'code'          => $request->code,
                'asset_type_id' => $request->asset_type_id,
                'name'          => $request->name,
                'brand'         => $request->brand,
                'model'         => $request->model,
                'color'         => $request->color,
                'status'        => $request->status,
            ]);

            // accessories
            $existingIds = $asset->accessories()
                ->pluck('id')
                ->toArray();

            $incomingIds = collect($request->accessories ?? [])
                ->pluck('id')
                ->filter()
                ->toArray();

            // Soft delete removed ones
            $idsToDelete = array_diff($existingIds, $incomingIds);

            if (! empty($idsToDelete)) {
                $asset->accessories()
                    ->whereIn('id', $idsToDelete)
                    ->delete(); // soft delete
            }

            // Process incoming accessories
            foreach ($request->accessories ?? [] as $item) {

                if (! empty($item['id'])) {
                    // Update existing
                    $asset->accessories()
                        ->where('id', $item['id'])
                        ->update([
                            'name' => trim($item['name']),
                        ]);
                } else {
                    // Create new
                    $asset->accessories()->create([
                        'name' => trim($item['name']),
                    ]);
                }
            }

            //defects
            $existingIds = $asset->defects()
                ->pluck('id')
                ->toArray();

            $incomingIds = collect($request->defects ?? [])
                ->pluck('id')
                ->filter()
                ->toArray();

            $idsToDelete = array_diff($existingIds, $incomingIds);

            if (! empty($idsToDelete)) {
                $asset->defects()
                    ->whereIn('id', $idsToDelete)
                    ->delete(); // soft delete
            }

            foreach ($request->defects ?? [] as $item) {

                if (! empty($item['id'])) {
                    $asset->defects()
                        ->where('id', $item['id'])
                        ->update([
                            'name' => trim($item['name']),
                        ]);
                } else {
                    $asset->defects()->create([
                        'name' => trim($item['name']),
                    ]);
                }
            }

        });

        return response()->json([
            'status'  => true,
            'message' => 'Asset updated successfully',
            'data'    => $asset->load(['accessories', 'defects']),
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $asset = Asset::find($id);
        if (! $asset) {
            return response()->json([
                'status'  => false,
                'message' => 'Asset not found',
            ], 404);
        }
        $asset->delete();
        return response()->json([
            'status'  => true,
            'message' => 'Asset deleted successfully.',
        ]);
    }
}
