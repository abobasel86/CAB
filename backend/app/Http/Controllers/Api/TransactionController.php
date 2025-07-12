<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\FieldSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        
        $query = Transaction::with('completedByUser');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('doctor_name', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json($transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->user()->canEdit()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $validatedData = $request->validate([
            'post_date' => 'nullable|date',
            'value_date' => 'nullable|date',
            'description' => 'nullable|string',
            'doctor_name' => 'nullable|string',
            'reference' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
            'specialist' => 'nullable|numeric',
            'registration' => 'nullable|numeric',
            'yearly' => 'nullable|numeric',
            'exam' => 'nullable|numeric',
            'certificate' => 'nullable|numeric',
            'newsletters' => 'nullable|numeric',
            'other' => 'nullable|numeric',
            'visa' => 'nullable|numeric',
            'inward_number' => 'nullable|string',
            'inward_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $transaction = Transaction::create($validatedData);
        
        return response()->json($transaction, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        return response()->json($transaction->load('completedByUser'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $user = $request->user();
        
        // Check if user can edit
        if (!$user->canEdit()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        // Check if transaction is locked and user is not admin
        if ($transaction->is_locked && !$user->isAdmin()) {
            return response()->json(['message' => 'Transaction is locked'], Response::HTTP_FORBIDDEN);
        }

        $manualFields = FieldSetting::getManualFields();
        $importedFields = FieldSetting::getImportedFields();
        
        $validationRules = [];
        $updateData = [];

        // Allow editing manual fields for editors
        if ($user->isEditor()) {
            foreach ($manualFields as $field) {
                if ($request->has($field)) {
                    $validationRules[$field] = 'nullable|numeric';
                    $updateData[$field] = $request->input($field);
                }
            }
        }

        // Allow admins to edit all fields except calculated ones
        if ($user->isAdmin()) {
            foreach ($request->all() as $key => $value) {
                if (!in_array($key, ['unspecified', 'summary', 'commission', 'total', 'difference'])) {
                    if (in_array($key, ['post_date', 'value_date', 'inward_date'])) {
                        $validationRules[$key] = 'nullable|date';
                    } elseif (in_array($key, ['amount', 'balance', 'specialist', 'registration', 'yearly', 'exam', 'certificate', 'newsletters', 'other', 'visa'])) {
                        $validationRules[$key] = 'nullable|numeric';
                    } elseif (in_array($key, ['description', 'doctor_name', 'reference', 'inward_number', 'notes'])) {
                        $validationRules[$key] = 'nullable|string';
                    } elseif ($key === 'is_locked') {
                        $validationRules[$key] = 'boolean';
                    }
                    $updateData[$key] = $value;
                }
            }
        }

        $validatedData = $request->validate($validationRules);
        
        // Check if all manual fields are completed to auto-lock
        $allManualFieldsCompleted = true;
        foreach ($manualFields as $field) {
            if (array_key_exists($field, $updateData)) {
                if (empty($updateData[$field])) {
                    $allManualFieldsCompleted = false;
                    break;
                }
            } elseif (empty($transaction->$field)) {
                $allManualFieldsCompleted = false;
                break;
            }
        }

        if ($allManualFieldsCompleted && !$transaction->is_locked) {
            $updateData['is_locked'] = true;
            $updateData['completed_by_user_id'] = $user->id;
            $updateData['completed_at'] = now();
        }

        $transaction->update($updateData);
        
        return response()->json($transaction->fresh()->load('completedByUser'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $transaction->delete();
        
        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}
