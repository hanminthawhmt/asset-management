<?php
namespace App\Http\Controllers;

use App\Models\Approver;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions          = Transaction::whereNull('deleted_at')->with(['asset', 'approver'])->get();
        $formattedTransactions = $transactions->map(function ($transaction) {
            return [
                'id'          => $transaction->id,
                'code'        => $transaction->asset->code,
                'borrow_date' => $transaction->borrow_date,
                'return_date' => $transaction->return_date,
                'status'      => $transaction->approval_status,
                'borrower'    => $transaction->borrower_name,
                'approver'    => $transaction->approver->name,
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $formattedTransactions,
        ]);
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
    public function show(Transaction $transaction)
    {
        $transaction->load([
            'asset.assetType',
            'asset.accessories',
            'asset.defects',
            'approver',
        ]);
        return response()->json([
            'status' => true,
            'data'   => [
                'id'                     => $transaction->id,
                'code'                   => $transaction->asset->code,
                'asset_type'             => $transaction->asset->assetType->name,
                'name'                   => $transaction->asset->name,
                'model'                  => $transaction->asset->model,
                'color'                  => $transaction->asset->color,
                'accessories'            => $transaction->asset->accessories->pluck('name')->values(),
                'defects'                => $transaction->asset->defects->pluck('name')->values(),
                'borrower'               => $transaction->borrower_name,
                'approver'               => [
                    $transaction->approver->id,
                    $transaction->approver->name,
                ],
                'recorded_date'          => $transaction->recorded_date,
                'borrow_date'            => $transaction->borrow_date,
                'return_date'            => $transaction->return_date,
                'approval_status'        => $transaction->approval_status,

                'property_front_image'   => $transaction->property_front_image,
                'property_back_image'    => $transaction->property_back_image,
                'property_left_image'    => $transaction->property_left_image,
                'property_right_image'   => $transaction->property_right_image,
                'property_overall_image' => $transaction->property_overall_image,

                'acc_front_image'        => $transaction->acc_front_image,
                'acc_back_image'         => $transaction->acc_back_image,
                'acc_left_image'         => $transaction->acc_left_image,
                'acc_right_image'        => $transaction->acc_right_image,
                'acc_overall_image'      => $transaction->acc_overall_image,
            ],
        ]);
    }

    public function updateApprovalStatus(Request $request)
    {
        $request->validate([
            'transaction_id'  => 'required|exists:transactions,id',
            'approver_id'     => 'required|exists:approvers,id',
            'approval_code'   => 'required|string',
            'approval_status' => 'required|in:APPROVED,REJECTED',
        ]);

        // find the transaction first
        $transaction = Transaction::findOrFail($request->transaction_id);

        // if the approval_status is not AWAITING
        if ($transaction->approval_status !== 'AWAITING') {
            return response()->json([
                'status'  => false,
                'message' => 'This transaction has already been processed.',
            ], 400);
        }

        // if the approver_id from request body is not the same with the approver_id from the transaction
        if ($transaction->approver_id !== $request->approver_id) {
            return response()->json([
                'status'  => false,
                'message' => 'You are not assigned to approve this transaction.',
            ], 403);
        }

        $approver = Approver::findOrFail($request->approver_id);

        if (! Hash::check($request->approval_code, $approver->approval_code)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid approval code.',
            ], 401);
        }

        $transaction->update([
            'approval_status' => $request->approval_status,
        ]);

        if ($request->approval_status === 'APPROVED') {
            $transaction->asset->update([
                'status' => 'BORROWED',
            ]);
        }

        if ($request->approval_status === 'REJECTED') {
            $transaction->asset->update([
                'status' => 'AVAILABLE',
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Approval status updated successfully.',
        ]);

    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'approver_id'   => 'required|exists:approvers,id',
            'approval_code' => 'required|string',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $transaction = Transaction::with('asset')->lockForUpdate()->findOrFail($id);

            if ($transaction->approval_status !== 'AWAITING') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Transaction already processed.',
                ], 400);
            }

            if ($transaction->approver_id != $request->approver_id) {
                return response()->json([
                    'status'  => false,
                    'message' => 'You are not assigned to approve this transaction.',
                ], 403);
            }

            $approver = Approver::findOrFail($request->approver_id);

            if (! Hash::check($request->approval_code, $approver->approval_code)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid approval code.',
                ], 401);
            }

            $transaction->update([
                'approval_status' => 'APPROVED',
            ]);

            $transaction->asset->update([
                'status' => 'BORROWED',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Transaction approved successfully.',
            ]);
        });
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'approver_id'   => 'required|exists:approvers,id',
            'approval_code' => 'required|string',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $transaction = Transaction::with('asset')->lockForUpdate()->findOrFail($id);

            if ($transaction->approval_status !== 'AWAITING') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Transaction already processed.',
                ], 400);
            }

            if ($transaction->approver_id != $request->approver_id) {
                return response()->json([
                    'status'  => false,
                    'message' => 'You are not assigned to reject this transaction.',
                ], 403);
            }

            $approver = Approver::findOrFail($request->approver_id);

            if (! Hash::check($request->approval_code, $approver->approval_code)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid approval code.',
                ], 401);
            }

            $transaction->update([
                'approval_status' => 'REJECTED',
            ]);

            $transaction->asset->update([
                'status' => 'AVAILABLE',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Transaction rejected successfully.',
            ]);

        });
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
