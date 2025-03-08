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
     * List all invoices with pagination
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['business:id,name,registration_number', 'debtor:id,name,kra_pin,status']);

        if ($request->has('business_id')) {
            $query->where('business_id', $request->business_id);
        }

        if ($request->has('debtor_id')) {
            $query->where('debtor_id', $request->debtor_id);
        }

        if ($request->has('is_overdue')) {
            $isOverdue = $request->boolean('is_overdue');
            if ($isOverdue) {
                $query->where('due_date', '<', now());
            } else {
                $query->where('due_date', '>=', now());
            }
        }

        $perPage = $request->per_page ?? 15;
        $invoices = $query->orderBy('invoice_date', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $invoices,
            'pagination' => [
                'total' => $invoices->total(),
                'per_page' => $invoices->perPage(),
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage()
            ]
        ]);
    }

    /**
     * Get invoices by debtor KRA PIN.
     */
    public function getByDebtorKra($kra_pin)
    {
        $debtor = Debtor::where('kra_pin', $kra_pin)->first();

        if (!$debtor) {
            return response()->json([
                'success' => false,
                'message' => 'Debtor not found with KRA PIN: ' . $kra_pin
            ], 404);
        }

        $invoices = Invoice::where('debtor_id', $debtor->id)
            ->with(['business:id,name,registration_number,email,phone'])
            ->orderBy('invoice_date', 'desc')
            ->get();

        $formattedInvoices = $invoices->map(function($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date,
                'due_date' => $invoice->due_date,
                'invoice_amount' => $invoice->invoice_amount,
                'due_amount' => $invoice->due_amount,
                'payment_terms' => $invoice->payment_terms,
                'days_overdue' => $invoice->days_overdue,
                'dbt_ratio' => $invoice->dbt_ratio,
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

        $totalDebt = $invoices->sum('due_amount');

        $businessRelationships = DB::table('business_debtor')
            ->where('debtor_id', $debtor->id)
            ->join('businesses', 'businesses.id', '=', 'business_debtor.business_id')
            ->select([
                'businesses.id as business_id',
                'businesses.name as business_name',
                'businesses.registration_number as business_kra_pin',
                'amount_owed',
                'average_payment_terms',
                'median_payment_terms',
                'average_days_overdue',
                'median_days_overdue',
                'average_dbt_ratio',
                'median_dbt_ratio'
            ])
            ->get();

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
                'business_relationships' => $businessRelationships,
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
        $business = Business::where('registration_number', $kra_pin)->first();

        if (!$business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found with KRA PIN: ' . $kra_pin
            ], 404);
        }

        $invoices = Invoice::where('business_id', $business->id)
            ->with(['debtor:id,name,kra_pin,email,status'])
            ->orderBy('invoice_date', 'desc')
            ->get();

        $formattedInvoices = $invoices->map(function($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'invoice_amount' => $invoice->invoice_amount,
                'due_amount' => $invoice->due_amount,
                'payment_terms' => $invoice->payment_terms,
                'days_overdue' => $invoice->days_overdue,
                'dbt_ratio' => $invoice->dbt_ratio,
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

        $groupedByDebtor = $formattedInvoices->groupBy('debtor.id');
        $debtorSummaries = [];

        foreach ($groupedByDebtor as $debtorId => $debtorInvoices) {
            $firstInvoice = $debtorInvoices->first();
            $totalOwed = $debtorInvoices->sum('due_amount');

            $relationship = DB::table('business_debtor')
                ->where('business_id', $business->id)
                ->where('debtor_id', $debtorId)
                ->first();

            $debtorSummaries[] = [
                'debtor' => [
                    'id' => $firstInvoice['debtor']['id'],
                    'name' => $firstInvoice['debtor']['name'],
                    'kra_pin' => $firstInvoice['debtor']['kra_pin'],
                    'email' => $firstInvoice['debtor']['email'],
                    'status' => $firstInvoice['debtor']['status']
                ],
                'metrics' => [
                    'average_payment_terms' => $relationship->average_payment_terms ?? null,
                    'median_payment_terms' => $relationship->median_payment_terms ?? null,
                    'average_days_overdue' => $relationship->average_days_overdue ?? null,
                    'median_days_overdue' => $relationship->median_days_overdue ?? null,
                    'average_dbt_ratio' => $relationship->average_dbt_ratio ?? null,
                    'median_dbt_ratio' => $relationship->median_dbt_ratio ?? null,
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
        $data = $request->has('invoices') ? $request->input('invoices') : [$request->all()];

        $results = [
            'success' => true,
            'created' => [],
            'skipped' => [],
            'errors' => []
        ];

        foreach ($data as $index => $invoiceData) {
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
                $existingInvoice = Invoice::where('invoice_number', $invoiceData['invoice_reference'])->first();
                if ($existingInvoice) {
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

                $business = Business::where('registration_number', $invoiceData['supplier_id'])->first();
                if (!$business) {
                    $results['errors'][] = [
                        'index' => $index,
                        'invoice_reference' => $invoiceData['invoice_reference'],
                        'message' => 'Business with KRA PIN ' . $invoiceData['supplier_id'] . ' not found'
                    ];
                    continue;
                }

                $debtor = Debtor::where('kra_pin', $invoiceData['debtor_id'])->first();
                if (!$debtor) {
                    $results['errors'][] = [
                        'index' => $index,
                        'invoice_reference' => $invoiceData['invoice_reference'],
                        'message' => 'Debtor with KRA PIN ' . $invoiceData['debtor_id'] . ' not found'
                    ];
                    continue;
                }

                if ($debtor->name !== $invoiceData['debtor_name']) {
                    $results['errors'][] = [
                        'index' => $index,
                        'invoice_reference' => $invoiceData['invoice_reference'],
                        'message' => 'Debtor name does not match the one in our records for KRA PIN ' . $invoiceData['debtor_id']
                    ];
                    continue;
                }

                DB::beginTransaction();
                try {
                    $invoice = Invoice::create([
                        'business_id' => $business->id,
                        'debtor_id' => $debtor->id,
                        'invoice_number' => $invoiceData['invoice_reference'],
                        'invoice_date' => $invoiceData['invoice_date'],
                        'due_date' => $invoiceData['due_date'],
                        'invoice_amount' => $invoiceData['invoice_amount'],
                        'due_amount' => $invoiceData['invoice_amount'],
                    ]);

                    $relation = DB::table('business_debtor')
                        ->where('business_id', $business->id)
                        ->where('debtor_id', $debtor->id)
                        ->first();

                    if ($relation) {
                        DB::table('business_debtor')
                            ->where('id', $relation->id)
                            ->update([
                                'amount_owed' => $relation->amount_owed + $invoiceData['invoice_amount'],
                                'updated_at' => now()
                            ]);
                    } else {
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

        $invoice->load('business', 'debtor');

        $businessDebtor = DB::table('business_debtor')
            ->where('business_id', $invoice->business_id)
            ->where('debtor_id', $invoice->debtor_id)
            ->first();

        $relatedInvoices = Invoice::where('business_id', $invoice->business_id)
            ->where('debtor_id', $invoice->debtor_id)
            ->where('id', '!=', $invoice->id)
            ->get([
                'id',
                'invoice_number',
                'invoice_date',
                'due_date',
                'invoice_amount',
                'due_amount',
                'payment_terms',
                'days_overdue',
                'dbt_ratio'
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'invoice' => $invoice,
                'total_amount_owed' => $businessDebtor ? $businessDebtor->amount_owed : 0,
                'business_debtor_metrics' => $businessDebtor ? [
                    'average_payment_terms' => $businessDebtor->average_payment_terms,
                    'median_payment_terms' => $businessDebtor->median_payment_terms,
                    'average_days_overdue' => $businessDebtor->average_days_overdue,
                    'median_days_overdue' => $businessDebtor->median_days_overdue,
                    'average_dbt_ratio' => $businessDebtor->average_dbt_ratio,
                    'median_dbt_ratio' => $businessDebtor->median_dbt_ratio,
                ] : null,
                'related_invoices' => $relatedInvoices
            ]
        ]);
    }
}
