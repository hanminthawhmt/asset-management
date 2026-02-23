<?php
namespace App\Http\Controllers;

use App\Models\Approver;
use App\Models\AssetType;

class FormOptionController extends Controller
{
    public function getPage()
    {
        $assetTypes = AssetType::where('deleted_at', null)->select('id', 'name')->orderBy('name')->get();
        $approvers  = Approver::where('deleted_at', null)->select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'status'      => true,
            'asset_types' => $assetTypes,
            'approvers'   => $approvers,
        ]);
    }
}
