<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\ServicePlan;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function csv($table)
    {
        $data = $this->getData($table, request()->all());
        $filename = $table . '_export_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = $this->getColumns($table);

        return response()->stream(function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, array_keys($columns));

            foreach ($data as $item) {
                $row = [];
                foreach ($columns as $key => $label) {
                    $val = data_get($item, $key);
                    // Format dates/currency if needed
                    if(str_contains($key, 'amount') || str_contains($key, 'balance')) {
                        $val = number_format((float)$val, 2);
                    }
                    $row[] = $val;
                }
                fputcsv($file, $row);
            }

            fclose($file);
        }, 200, $headers);
    }

    public function pdf($table)
    {
        $data = $this->getData($table, request()->all());
        $columns = $this->getColumns($table);
        $title = ucfirst(str_replace('_', ' ', $table)) . ' Report';

        // Filter summary text
        if(request('start_date') || request('end_date')) {
            $title .= ' (' . (request('start_date') ?? 'Start') . ' to ' . (request('end_date') ?? 'Now') . ')';
        }

        $pdf = Pdf::loadView('exports.pdf_generic', compact('data', 'columns', 'title'));
        return $pdf->download($table . '_export_' . date('Y-m-d_H-i') . '.pdf');
    }

    private function getData($table, $filters = [])
    {
        $query = null;

        switch ($table) {
            case 'users':
                $query = User::with('roles')->latest();
                break;

            case 'customers':
                $query = Customer::with(['subscription.servicePlan', 'zone'])->latest();
                if (!empty($filters['search'])) {
                    $query->where('name', 'like', '%' . $filters['search'] . '%')
                          ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
                }
                if (!empty($filters['zone_id'])) {
                    $query->where('zone_id', $filters['zone_id']);
                }
                if (!empty($filters['status'])) {
                    if($filters['status'] === 'active') $query->where('is_active', true);
                    if($filters['status'] === 'inactive') $query->where('is_active', false);
                }
                break;

            case 'service_plans':
                $query = ServicePlan::query();
                break;

            case 'invoices':
                $query = Invoice::with(['customer', 'payments'])->latest();
                if (!empty($filters['search'])) {
                    $query->where('invoice_number', 'like', '%' . $filters['search'] . '%')
                          ->orWhereHas('customer', function($q) use ($filters) {
                              $q->where('name', 'like', '%' . $filters['search'] . '%');
                          });
                }
                if (!empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }
                if (!empty($filters['start_date'])) {
                     $query->whereDate('created_at', '>=', $filters['start_date']);
                }
                if (!empty($filters['end_date'])) {
                     $query->whereDate('created_at', '<=', $filters['end_date']);
                }
                break;

            case 'payments':
                $query = Payment::with(['invoice.customer', 'recordedBy'])->latest();
                if (!empty($filters['search'])) {
                    $query->where('reference', 'like', '%' . $filters['search'] . '%')
                          ->orWhereHas('customer', function($q) use ($filters) {
                              $q->where('name', 'like', '%' . $filters['search'] . '%');
                          });
                }
                if (!empty($filters['channel'])) {
                    $query->where('channel', $filters['channel']);
                }
                if (!empty($filters['start_date'])) {
                     $query->whereDate('paid_at', '>=', $filters['start_date']);
                }
                if (!empty($filters['end_date'])) {
                     $query->whereDate('paid_at', '<=', $filters['end_date']);
                }
                break;
            
            case 'cash_approvals':
                // Payments that are Cash and have NOT been approved (or approved if filtering history)
                // Assuming this export is for the "Pending Cash" view context primarily
                $query = Payment::with(['invoice.customer', 'recordedBy'])
                        ->where('channel', 'Cash')
                        ->whereNull('approved_at')
                        ->latest();
                break;

            case 'expenses':
                $query = Expense::with(['category', 'vendor', 'zone', 'recordedBy'])->latest('expense_date');
                if (!empty($filters['search'])) {
                    $s = $filters['search'];
                    $query->where(function ($q) use ($s) {
                        $q->where('expense_number', 'like', "%{$s}%")
                          ->orWhere('description', 'like', "%{$s}%")
                          ->orWhere('reference', 'like', "%{$s}%");
                    });
                }
                if (!empty($filters['status']))         $query->where('status', $filters['status']);
                if (!empty($filters['category_id']))    $query->where('expense_category_id', $filters['category_id']);
                if (!empty($filters['vendor_id']))      $query->where('vendor_id', $filters['vendor_id']);
                if (!empty($filters['zone_id']))        $query->where('zone_id', $filters['zone_id']);
                if (!empty($filters['payment_method'])) $query->where('payment_method', $filters['payment_method']);
                if (!empty($filters['start_date']))     $query->whereDate('expense_date', '>=', $filters['start_date']);
                if (!empty($filters['end_date']))       $query->whereDate('expense_date', '<=', $filters['end_date']);
                break;

            case 'vendors':
                $query = Vendor::withCount('expenses')->withSum('expenses as total_spent', 'total_amount');
                if (!empty($filters['search'])) {
                    $s = $filters['search'];
                    $query->where(function ($q) use ($s) {
                        $q->where('name', 'like', "%{$s}%")
                          ->orWhere('phone', 'like', "%{$s}%")
                          ->orWhere('email', 'like', "%{$s}%");
                    });
                }
                if (!empty($filters['status'])) {
                    $query->where('is_active', $filters['status'] === 'active');
                }
                break;

            default:
                abort(404);
        }

        return $query->get();
    }

    private function getColumns($table)
    {
        switch ($table) {
            case 'users':
                return [
                    'name' => 'Name',
                    'email' => 'Email',
                    'roles.0.name' => 'Role',
                    'created_at' => 'Joined Date'
                ];
            case 'customers':
                return [
                    'name' => 'Name',
                    'phone' => 'Phone',
                    'type' => 'Type',
                    'zone.name' => 'Zone',
                    'subscription.servicePlan.name' => 'Plan',
                    'is_active' => 'Active',
                    'balance' => 'Balance'
                ];
            case 'service_plans':
                return [
                    'name' => 'Name',
                    'amount' => 'Amount',
                    'billing_cycle' => 'Cycle',
                    'description' => 'Description'
                ];
            case 'invoices':
                return [
                    'invoice_number' => 'Invoice #',
                    'customer.name' => 'Customer',
                    'amount' => 'Total',
                    'amount_paid' => 'Paid',
                    'balance' => 'Balance',
                    'status' => 'Status',
                    'due_date' => 'Due Date'
                ];
            case 'payments':
                return [
                    'reference' => 'Ref',
                    'invoice.customer.name' => 'Customer',
                    'amount' => 'Amount',
                    'payment_method' => 'Method',
                    'recorded_by.name' => 'Recorded By',
                    'paid_at' => 'Date'
                ];
            case 'cash_approvals':
                return [
                    'reference' => 'Ref',
                    'invoice.customer.name' => 'Customer',
                    'amount' => 'Amount',
                    'recorded_by.name' => 'Collected By',
                    'paid_at' => 'Date Collected'
                ];
            case 'expenses':
                return [
                    'expense_number'  => 'Expense #',
                    'expense_date'    => 'Date',
                    'category.name'   => 'Category',
                    'vendor.name'     => 'Vendor',
                    'zone.name'       => 'Zone',
                    'description'     => 'Description',
                    'amount'          => 'Amount',
                    'tax_amount'      => 'Tax',
                    'total_amount'    => 'Total',
                    'payment_method'  => 'Method',
                    'status'          => 'Status',
                    'recorded_by.name'=> 'Recorded By',
                ];
            case 'vendors':
                return [
                    'name'           => 'Name',
                    'contact_person' => 'Contact',
                    'phone'          => 'Phone',
                    'email'          => 'Email',
                    'tax_id'         => 'Tax ID',
                    'expenses_count' => '# Expenses',
                    'total_spent'    => 'Total Spent',
                    'is_active'      => 'Active',
                ];
            default:
                return [];
        }
    }
}
