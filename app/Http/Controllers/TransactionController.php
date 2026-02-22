<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'asset_id'               => 'required|exists:assets,id',
            'approver_id'            => 'required|exists:approvers,id',
            'borrower_name'          => 'required|string',
            'recorded_date'          => 'required|date',
            'borrow_date'            => 'required|date',
            'return_date'            => 'nullable|date',
            'property_front_image'   => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'property_back_image'    => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'property_left_image'    => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'property_right_image'   => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'property_overall_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',

            'acs_front_image'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'acs_back_image'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'acs_left_image'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'acs_right_image'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'acs_overall_image'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        try {

            $transaction = Transaction::create([
                'asset_id'      => $request->asset_id,
                'approver_id'   => $request->approver_id,
                'borrower_name' => $request->borrower_name,
                'recorded_date' => $request->recorded_date,
                'borrow_date'   => $request->borrow_date,
                'return_date'   => $request->return_date,
            ]);

            $this->uploadImages($request, $transaction);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'A borrow request has been successfully created',
                'data'    => $transaction,
            ], 201);

        } catch (\Exception $e) {

            DB::rollback();
            return response()->json([
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ], 500);

        }
    }

    private function uploadImages(Request $request, Transaction $transaction)
    {
        $imageFields = [
            'property_front_image',
            'property_back_image',
            'property_left_image',
            'property_right_image',
            'property_overall_image',
            'acc_front_image',
            'acc_back_image',
            'acc_left_image',
            'acc_right_image',
            'acc_overall_image',
        ];

        $uploadedData = [];

        foreach ($imageFields as $field) {
            $file = $request->file($field);
            if ($file && $file->isValid()) {
                $result = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::uploadApi()
                    ->upload($file->getPathname(), [
                        'folder' => 'asset-transactions/' . $transaction->id,
                    ]);

                $uploadedData[$field] = $result['secure_url'];
            }
        }
        if (! empty($uploadedData)) {
            $transaction->update($uploadedData);
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
