<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Debtor;
use App\Models\Invoice;
use Illuminate\Http\Request;

class DebtorController extends Controller
{
    /**
     * Get invoices by debtor KRA PIN.
     */
    public function getByDebtorKra($kra_pin)
    {
        // Find the debtor by KRA PIN
        $debtor = Debtor::where('kra_pin', $kra_pin)->first();

        if (!$debtor) {
            return response()->json([
                'success' => false,
                'message' => 'Debtor not found with KRA PIN: ' . $kra_pin
            ], 404);
        }

        // Get all invoices for this debtor
        $invoices = Invoice::where('debtor_id', $debtor->id)
            ->with(['business:id,name,registration_number,email,phone'])
            ->orderBy('invoice_date', 'desc')
            ->get();

        // Format the response
        $formattedInvoices = $invoices->map(function($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date,
                'due_date' => $invoice->due_date,
                'invoice_amount' => $invoice->invoice_amount,
                'due_amount' => $invoice->due_amount,
                'business' => [
                    'id' => $invoice->business->id,
                    'name' => $invoice->business->name,
                    'kra_pin' => $invoice->business->registration_number,
                    'email' => $invoice->business->email,
                    'phone' => $invoice->business->phone,
                ],
                'is_overdue' => $invoice->isOverdue(),
                'days_to_due' => $invoice->daysUntilDue()
            ];
        });

        // Calculate total debt
        $totalDebt = $invoices->sum('due_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'debtor' => [
                    'id' => $debtor->id,
                    'name' => $debtor->name,
                    'kra_pin' => $debtor->kra_pin,
                    'email' => $debtor->email,
                    'status' => $debtor->status
                ],
                'invoices' => $formattedInvoices,
                'total_count' => $formattedInvoices->count(),
                'total_debt' => $totalDebt
            ]
        ]);
    }

    /**
     * Get invoices by business KRA PIN.
     */
    public function getByBusinessKra($kra_pin)
    {
        // Find the business by KRA PIN
        $business = Business::where('registration_number', $kra_pin)->first();

        if (!$business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found with KRA PIN: ' . $kra_pin
            ], 404);
        }

        // Get all invoices issued by this business
        $invoices = Invoice::where('business_id', $business->id)
            ->with(['debtor:id,name,kra_pin,email,status'])
            ->orderBy('invoice_date', 'desc')
            ->get();

        // Format the response
        $formattedInvoices = $invoices->map(function($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'invoice_amount' => $invoice->invoice_amount,
                'due_amount' => $invoice->due_amount,
                'debtor' => [
                    'id' => $invoice->debtor->id,
                    'name' => $invoice->debtor->name,
                    'kra_pin' => $invoice->debtor->kra_pin,
                    'email' => $invoice->debtor->email,
                    'status' => $invoice->debtor->status
                ],
                'is_overdue' => $invoice->isOverdue(),
                'days_to_due' => $invoice->daysUntilDue()
            ];
        });

        // Group invoices by debtor for a more organized response
        $groupedByDebtor = $formattedInvoices->groupBy('debtor.id');
        $debtorSummaries = [];

        foreach ($groupedByDebtor as $debtorId => $debtorInvoices) {
            $firstInvoice = $debtorInvoices->first();
            $totalOwed = $debtorInvoices->sum('due_amount');

            $debtorSummaries[] = [
                'debtor' => [
                    'id' => $firstInvoice['debtor']['id'],
                    'name' => $firstInvoice['debtor']['name'],
                    'kra_pin' => $firstInvoice['debtor']['kra_pin'],
                    'email' => $firstInvoice['debtor']['email'],
                    'status' => $firstInvoice['debtor']['status']
                ],
                'invoices' => $debtorInvoices,
                'invoice_count' => $debtorInvoices->count(),
                'total_owed' => $totalOwed
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'business' => [
                    'id' => $business->id,
                    'name' => $business->name,
                    'kra_pin' => $business->registration_number,
                    'email' => $business->email,
                    'phone' => $business->phone
                ],
                'debtor_summaries' => $debtorSummaries,
                'total_invoice_count' => $formattedInvoices->count(),
                'total_amount_owed' => $formattedInvoices->sum('due_amount')
            ]
        ]);
    }

    /**
     * Get a debtor by KRA PIN with related business data (without invoices).
     */
    public function show($kra_pin)
    {
        $debtor = Debtor::where('kra_pin', $kra_pin)->first();

        if (!$debtor) {
            return response()->json([
                'success' => false,
                'message' => 'Debtor not found'
            ], 404);
        }

        // Load business relationships without invoices
        $debtor->load('businesses');

        // Format business data
        $businessData = [];
        foreach ($debtor->businesses as $business) {
            $businessData[] = [
                'business_id' => $business->id,
                'business_name' => $business->name,
                'business_kra_pin' => $business->registration_number,
                'amount_owed' => $business->pivot->amount_owed
            ];
        }

        // Check if this debtor is also a business
        $isAlsoBusiness = $debtor->isBusiness();
        $businessRecord = $isAlsoBusiness ? $debtor->asBusiness() : null;

        return response()->json([
            'success' => true,
            'data' => [
                'debtor' => [
                    'id' => $debtor->id,
                    'name' => $debtor->name,
                    'kra_pin' => $debtor->kra_pin,
                    'email' => $debtor->email,
                    'status' => $debtor->status,
                    'is_also_business' => $isAlsoBusiness
                ],
                'business_relationships' => $businessData,
                'total_debt' => $debtor->getTotalAmountOwed()
            ],
            'business_record' => $businessRecord
        ]);
    }

    /**
     * List all debtors with their business relationships.
     */
    public function index(Request $request)
    {
        $query = Debtor::with('businesses');

        // Apply filters if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('business_id')) {
            $query->whereHas('businesses', function($q) use ($request) {
                $q->where('businesses.id', $request->business_id);
            });
        }

        // Apply pagination
        $perPage = $request->per_page ?? 15;
        $debtors = $query->paginate($perPage);

        // Format the response
        $formattedDebtors = $debtors->map(function($debtor) {
            $businessData = $debtor->businesses->map(function($business) {
                return [
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'business_kra_pin' => $business->registration_number,
                    'amount_owed' => $business->pivot->amount_owed
                ];
            });

            return [
                'id' => $debtor->id,
                'name' => $debtor->name,
                'kra_pin' => $debtor->kra_pin,
                'email' => $debtor->email,
                'status' => $debtor->status,
                'business_relationships' => $businessData,
                'total_debt' => $debtor->getTotalAmountOwed(),
                'is_also_business' => $debtor->isBusiness()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedDebtors,
            'pagination' => [
                'total' => $debtors->total(),
                'per_page' => $debtors->perPage(),
                'current_page' => $debtors->currentPage(),
                'last_page' => $debtors->lastPage()
            ]
        ]);
    }

    /**
     * Search for debtors by name or KRA PIN.
     */
    public function search(Request $request)
    {
        $validator = $request->validate([
            'query' => 'required|string|min:3'
        ]);

        $query = $request->query;

        $debtors = Debtor::where('name', 'LIKE', "%{$query}%")
            ->orWhere('kra_pin', 'LIKE', "%{$query}%")
            ->with('businesses')
            ->limit(10)
            ->get();

        // Format the response similar to index method
        $formattedDebtors = $debtors->map(function($debtor) {
            $businessData = $debtor->businesses->map(function($business) {
                return [
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'business_kra_pin' => $business->registration_number,
                    'amount_owed' => $business->pivot->amount_owed
                ];
            });

            return [
                'id' => $debtor->id,
                'name' => $debtor->name,
                'kra_pin' => $debtor->kra_pin,
                'email' => $debtor->email,
                'status' => $debtor->status,
                'business_relationships' => $businessData,
                'total_debt' => $debtor->getTotalAmountOwed(),
                'is_also_business' => $debtor->isBusiness()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedDebtors
        ]);
    }
}
