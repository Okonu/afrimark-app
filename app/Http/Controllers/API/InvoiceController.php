<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Debtor;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
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
     * Store invoice records.
     * Can handle both single and multiple records.
     */
    public function store(Request $request)
    {
        // Check if data is an array of records or a single record
        $data = $request->has('invoices') ? $request->input('invoices') : [$request->all()];

        // Results tracking
        $results = [
            'success' => true,
            'created' => [],
            'skipped' => [],
            'errors' => []
        ];

        // Process each invoice record
        foreach ($data as $index => $invoiceData) {
            // Define validation rules
            $validator = Validator::make($invoiceData, [
                'supplier_id' => 'required|string', // Business KRA PIN
                'debtor_id' => 'required|string',   // Debtor KRA PIN
                'debtor_name' => 'required|string',
                'invoice_amount' => 'required|numeric|min:0',
                'due_date' => 'required|date',
                'invoice_date' => 'required|date',
                'invoice_reference' => 'required|string'
            ]);

            if ($validator->fails()) {
                $results['errors'][] = [
                    'index' => $index,
                    'invoice_reference' => $invoiceData['invoice_reference'] ?? null,
                    'errors' => $validator->errors()->toArray()
                ];
                continue;
            }

            try {
                // Check if invoice number already exists
                $existingInvoice = Invoice::where('invoice_number', $invoiceData['invoice_reference'])->first();
                if ($existingInvoice) {
                    // Instead of treating as an error, add to a separate 'skipped' category
                    if (!isset($results['skipped'])) {
                        $results['skipped'] = [];
                    }

                    $results['skipped'][] = [
                        'index' => $index,
                        'invoice_reference' => $invoiceData['invoice_reference'],
                        'message' => 'Invoice with reference ' . $invoiceData['invoice_reference'] . ' already exists'
                    ];
                    continue;
                }

                // Find the business (supplier) - don't create if it doesn't exist
                $business = Business::where('registration_number', $invoiceData['supplier_id'])->first();
                if (!$business) {
                    $results['errors'][] = [
                        'index' => $index,
                        'invoice_reference' => $invoiceData['invoice_reference'],
                        'message' => 'Business with KRA PIN ' . $invoiceData['supplier_id'] . ' not found'
                    ];
                    continue;
                }

                // Find the debtor - don't create if it doesn't exist
                $debtor = Debtor::where('kra_pin', $invoiceData['debtor_id'])->first();
                if (!$debtor) {
                    $results['errors'][] = [
                        'index' => $index,
                        'invoice_reference' => $invoiceData['invoice_reference'],
                        'message' => 'Debtor with KRA PIN ' . $invoiceData['debtor_id'] . ' not found'
                    ];
                    continue;
                }

                // Check if debtor name matches
                if ($debtor->name !== $invoiceData['debtor_name']) {
                    $results['errors'][] = [
                        'index' => $index,
                        'invoice_reference' => $invoiceData['invoice_reference'],
                        'message' => 'Debtor name does not match the one in our records for KRA PIN ' . $invoiceData['debtor_id']
                    ];
                    continue;
                }

                // Process the valid invoice
                DB::beginTransaction();
                try {
                    // Create the invoice
                    $invoice = Invoice::create([
                        'business_id' => $business->id,
                        'debtor_id' => $debtor->id,
                        'invoice_number' => $invoiceData['invoice_reference'],
                        'invoice_date' => $invoiceData['invoice_date'],
                        'due_date' => $invoiceData['due_date'],
                        'invoice_amount' => $invoiceData['invoice_amount'],
                        'due_amount' => $invoiceData['invoice_amount'], // Initially set to invoice amount
                    ]);

                    // Create or update the business_debtor relationship
                    $relation = DB::table('business_debtor')
                        ->where('business_id', $business->id)
                        ->where('debtor_id', $debtor->id)
                        ->first();

                    if ($relation) {
                        // Update existing relation by adding the new invoice amount
                        DB::table('business_debtor')
                            ->where('id', $relation->id)
                            ->update([
                                'amount_owed' => $relation->amount_owed + $invoiceData['invoice_amount'],
                                'updated_at' => now()
                            ]);
                    } else {
                        // Create new relation
                        DB::table('business_debtor')->insert([
                            'business_id' => $business->id,
                            'debtor_id' => $debtor->id,
                            'amount_owed' => $invoiceData['invoice_amount'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::commit();

                    $results['created'][] = [
                        'index' => $index,
                        'invoice_id' => $invoice->id,
                        'invoice_reference' => $invoice->invoice_number
                    ];

                } catch (\Exception $e) {
                    DB::rollBack();
                    $results['errors'][] = [
                        'index' => $index,
                        'invoice_reference' => $invoiceData['invoice_reference'],
                        'message' => 'Failed to create invoice: ' . $e->getMessage()
                    ];
                }

            } catch (\Exception $e) {
                $results['errors'][] = [
                    'index' => $index,
                    'message' => 'Error processing invoice: ' . $e->getMessage()
                ];
            }
        }

        // Update success flag if we have any errors
        if (!empty($results['errors'])) {
            $results['success'] = false;
        }

        return response()->json($results);
    }

    /**
     * Get a specific invoice by invoice number with related data.
     */
    public function show($invoice_number)
    {
        $invoice = Invoice::where('invoice_number', $invoice_number)->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        // Load related data
        $invoice->load('business', 'debtor');

        // Get debt relationship details
        $businessDebtor = DB::table('business_debtor')
            ->where('business_id', $invoice->business_id)
            ->where('debtor_id', $invoice->debtor_id)
            ->first();

        // Get other invoices from the same business to this debtor
        $relatedInvoices = Invoice::where('business_id', $invoice->business_id)
            ->where('debtor_id', $invoice->debtor_id)
            ->where('id', '!=', $invoice->id)
            ->get(['id', 'invoice_number', 'invoice_date', 'due_date', 'invoice_amount', 'due_amount']);

        return response()->json([
            'success' => true,
            'data' => [
                'invoice' => $invoice,
                'total_amount_owed' => $businessDebtor ? $businessDebtor->amount_owed : 0,
                'related_invoices' => $relatedInvoices
            ]
        ]);
    }
}
