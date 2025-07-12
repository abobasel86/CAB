<?php

namespace App\Imports;

use App\Models\Transaction;
use App\Models\FieldSetting;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class TransactionsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(Collection $rows)
    {
        $importedFields = FieldSetting::getImportedFields();
        
        foreach ($rows as $row) {
            $transactionData = [];
            
            // Only import fields marked as 'imported' in field settings
            foreach ($importedFields as $field) {
                if (isset($row[$field])) {
                    $value = $row[$field];
                    
                    // Handle date fields
                    if (in_array($field, ['post_date', 'value_date', 'inward_date']) && $value) {
                        try {
                            $transactionData[$field] = Carbon::parse($value)->format('Y-m-d');
                        } catch (\Exception $e) {
                            $transactionData[$field] = null;
                        }
                    } 
                    // Handle numeric fields
                    elseif (in_array($field, ['amount', 'balance', 'specialist'])) {
                        $transactionData[$field] = is_numeric($value) ? (float)$value : 0;
                    }
                    // Handle text fields
                    else {
                        $transactionData[$field] = $value;
                    }
                }
            }
            
            // Set default values for manual fields
            $manualFields = ['registration', 'yearly', 'exam', 'certificate', 'newsletters', 'other', 'visa'];
            foreach ($manualFields as $field) {
                $transactionData[$field] = 0;
            }
            
            Transaction::create($transactionData);
        }
    }

    public function rules(): array
    {
        return [
            'post_date' => 'nullable|date',
            'value_date' => 'nullable|date',
            'description' => 'nullable|string',
            'doctor_name' => 'nullable|string',
            'reference' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
            'specialist' => 'nullable|numeric',
        ];
    }
}
