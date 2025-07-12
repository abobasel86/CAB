<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Transaction::with('completedByUser');
        
        if (isset($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('doctor_name', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if (isset($this->filters['date_from'])) {
            $query->where('post_date', '>=', $this->filters['date_from']);
        }

        if (isset($this->filters['date_to'])) {
            $query->where('post_date', '<=', $this->filters['date_to']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Post Date',
            'Value Date',
            'Description',
            'Doctor Name',
            'Reference',
            'Amount',
            'Balance',
            'Specialist',
            'Registration',
            'Yearly',
            'Exam',
            'Certificate',
            'Newsletters',
            'Other',
            'Visa',
            'Unspecified',
            'Summary',
            'Commission',
            'Total',
            'Difference',
            'Inward Number',
            'Inward Date',
            'Notes',
            'Is Locked',
            'Completed By',
            'Completed At',
            'Created At'
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->post_date ? $transaction->post_date->format('Y-m-d') : '',
            $transaction->value_date ? $transaction->value_date->format('Y-m-d') : '',
            $transaction->description,
            $transaction->doctor_name,
            $transaction->reference,
            $transaction->amount,
            $transaction->balance,
            $transaction->specialist,
            $transaction->registration,
            $transaction->yearly,
            $transaction->exam,
            $transaction->certificate,
            $transaction->newsletters,
            $transaction->other,
            $transaction->visa,
            $transaction->unspecified,
            $transaction->summary,
            $transaction->commission,
            $transaction->total,
            $transaction->difference,
            $transaction->inward_number,
            $transaction->inward_date ? $transaction->inward_date->format('Y-m-d') : '',
            $transaction->notes,
            $transaction->is_locked ? 'Yes' : 'No',
            $transaction->completedByUser ? $transaction->completedByUser->name : '',
            $transaction->completed_at ? $transaction->completed_at->format('Y-m-d H:i:s') : '',
            $transaction->created_at->format('Y-m-d H:i:s')
        ];
    }
}
