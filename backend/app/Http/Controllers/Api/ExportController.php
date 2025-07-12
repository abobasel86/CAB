<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\TransactionsExport;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class ExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function exportToExcel(Request $request)
    {
        $filters = $request->only(['search', 'date_from', 'date_to']);
        
        $filename = 'transactions_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new TransactionsExport($filters), $filename);
    }

    public function exportToPdf(Request $request)
    {
        $filters = $request->only(['search', 'date_from', 'date_to']);
        
        $query = Transaction::with('completedByUser');
        
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('doctor_name', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if (isset($filters['date_from'])) {
            $query->where('post_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('post_date', '<=', $filters['date_to']);
        }

        $transactions = $query->get();
        
        $html = $this->generatePdfHtml($transactions);
        
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $filename = 'transactions_' . date('Y-m-d_H-i-s') . '.pdf';
        
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    private function generatePdfHtml($transactions)
    {
        $html = '
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 10px; direction: rtl; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 4px; text-align: center; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .header { text-align: center; margin-bottom: 20px; }
                .totals { margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>Bank Reconciliation Report</h2>
                <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Post Date</th>
                        <th>Description</th>
                        <th>Doctor Name</th>
                        <th>Amount</th>
                        <th>Registration</th>
                        <th>Yearly</th>
                        <th>Exam</th>
                        <th>Certificate</th>
                        <th>Newsletters</th>
                        <th>Other</th>
                        <th>Visa</th>
                        <th>Summary</th>
                        <th>Commission</th>
                        <th>Total</th>
                        <th>Difference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($transactions as $transaction) {
            $html .= '
                    <tr>
                        <td>' . $transaction->id . '</td>
                        <td>' . ($transaction->post_date ? $transaction->post_date->format('Y-m-d') : '') . '</td>
                        <td>' . $transaction->description . '</td>
                        <td>' . $transaction->doctor_name . '</td>
                        <td>' . number_format($transaction->amount, 2) . '</td>
                        <td>' . number_format($transaction->registration, 2) . '</td>
                        <td>' . number_format($transaction->yearly, 2) . '</td>
                        <td>' . number_format($transaction->exam, 2) . '</td>
                        <td>' . number_format($transaction->certificate, 2) . '</td>
                        <td>' . number_format($transaction->newsletters, 2) . '</td>
                        <td>' . number_format($transaction->other, 2) . '</td>
                        <td>' . number_format($transaction->visa, 2) . '</td>
                        <td>' . number_format($transaction->summary, 2) . '</td>
                        <td>' . number_format($transaction->commission, 2) . '</td>
                        <td>' . number_format($transaction->total, 2) . '</td>
                        <td>' . number_format($transaction->difference, 2) . '</td>
                        <td>' . ($transaction->is_locked ? 'Locked' : 'Open') . '</td>
                    </tr>';
        }
        
        $totalAmount = $transactions->sum('amount');
        $totalSummary = $transactions->sum('summary');
        $totalCommission = $transactions->sum('commission');
        
        $html .= '
                </tbody>
                <tfoot>
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td colspan="4">TOTALS</td>
                        <td>' . number_format($totalAmount, 2) . '</td>
                        <td>' . number_format($transactions->sum('registration'), 2) . '</td>
                        <td>' . number_format($transactions->sum('yearly'), 2) . '</td>
                        <td>' . number_format($transactions->sum('exam'), 2) . '</td>
                        <td>' . number_format($transactions->sum('certificate'), 2) . '</td>
                        <td>' . number_format($transactions->sum('newsletters'), 2) . '</td>
                        <td>' . number_format($transactions->sum('other'), 2) . '</td>
                        <td>' . number_format($transactions->sum('visa'), 2) . '</td>
                        <td>' . number_format($totalSummary, 2) . '</td>
                        <td>' . number_format($totalCommission, 2) . '</td>
                        <td>' . number_format($transactions->sum('total'), 2) . '</td>
                        <td>' . number_format($transactions->sum('difference'), 2) . '</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </body>
        </html>';
        
        return $html;
    }
}
