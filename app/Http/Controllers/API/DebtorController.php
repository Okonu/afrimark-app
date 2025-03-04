<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Debtor;
use Illuminate\Http\Request;

class DebtorController extends Controller
{
    /**
     * Get a debtor by KRA PIN with related data.
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

        // Load relationships
        $debtor->load('businesses');

        // Get all invoices for this debtor
        $invoices = $debtor->invoices()->with('business')->get();

        // Group invoices by business
        $invoicesByBusiness = $invoices->groupBy('business_id');

        // Format business data with their invoices
        $businessData = [];
        foreach ($debtor->businesses as $business) {
            $businessInvoices = $invoicesByBusiness->get($business->id, collect());

            $businessData[] = [
                'business_id' => $business->id,
                'business_name' => $business->name,
                'business_kra_pin' => $business->registration_number,
                'amount_owed' => $business->pivot->amount_owed,
                'invoices' => $businessInvoices->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_date' => $invoice->invoice_date,
                        'due_date' => $invoice->due_date,
                        'invoice_amount' => $invoice->invoice_amount,
                        'due_amount' => $invoice->due_amount,
                        'is_overdue' => $invoice->isOverdue(),
                        'days_to_due' => $invoice->daysUntilDue()
                    ];
                })
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
                'total_debt' => $debtor->getTotalAmountOwed(),
            ],
            'business_record' => $businessRecord
        ]);
    }
}
