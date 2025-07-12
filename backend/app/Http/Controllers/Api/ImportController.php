<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\TransactionsImport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function importTransactions(Request $request)
    {
        if (!$request->user()->canImport()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new TransactionsImport, $request->file('file'));
            
            return response()->json([
                'message' => 'Transactions imported successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import failed: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'post_date',
            'value_date', 
            'description',
            'doctor_name',
            'reference',
            'amount',
            'balance',
            'specialist',
            'inward_number',
            'inward_date'
        ];

        $filename = 'transaction_template.csv';
        $handle = fopen('php://output', 'w');
        
        return response()->stream(function() use ($handle, $headers) {
            fputcsv($handle, $headers);
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}
