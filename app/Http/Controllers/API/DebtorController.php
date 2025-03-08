<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Debtor;
use Illuminate\Http\Request;

class DebtorController extends Controller
{
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

        $debtor->load('businesses');

        $businessData = [];
        foreach ($debtor->businesses as $business) {
            $businessData[] = [
                'business_id' => $business->id,
                'business_name' => $business->name,
                'business_kra_pin' => $business->registration_number,
                'amount_owed' => $business->pivot->amount_owed,
                'metrics' => [
                    'average_payment_terms' => $business->pivot->average_payment_terms,
                    'median_payment_terms' => $business->pivot->median_payment_terms,
                    'average_days_overdue' => $business->pivot->average_days_overdue,
                    'median_days_overdue' => $business->pivot->median_days_overdue,
                    'average_dbt_ratio' => $business->pivot->average_dbt_ratio,
                    'median_dbt_ratio' => $business->pivot->median_dbt_ratio,
                ]
            ];
        }

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

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('business_id')) {
            $query->whereHas('businesses', function($q) use ($request) {
                $q->where('businesses.id', $request->business_id);
            });
        }

        $perPage = $request->per_page ?? 15;
        $debtors = $query->paginate($perPage);

        $formattedDebtors = $debtors->map(function($debtor) {
            $businessData = $debtor->businesses->map(function($business) {
                return [
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'business_kra_pin' => $business->registration_number,
                    'amount_owed' => $business->pivot->amount_owed,
                    'metrics' => [
                        'average_payment_terms' => $business->pivot->average_payment_terms,
                        'median_payment_terms' => $business->pivot->median_payment_terms,
                        'average_days_overdue' => $business->pivot->average_days_overdue,
                        'median_days_overdue' => $business->pivot->median_days_overdue,
                        'average_dbt_ratio' => $business->pivot->average_dbt_ratio,
                        'median_dbt_ratio' => $business->pivot->median_dbt_ratio,
                    ]
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

        $formattedDebtors = $debtors->map(function($debtor) {
            $businessData = $debtor->businesses->map(function($business) {
                return [
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'business_kra_pin' => $business->registration_number,
                    'amount_owed' => $business->pivot->amount_owed,
                    'metrics' => [
                        'average_payment_terms' => $business->pivot->average_payment_terms,
                        'median_payment_terms' => $business->pivot->median_payment_terms,
                        'average_days_overdue' => $business->pivot->average_days_overdue,
                        'median_days_overdue' => $business->pivot->median_days_overdue,
                        'average_dbt_ratio' => $business->pivot->average_dbt_ratio,
                        'median_dbt_ratio' => $business->pivot->median_dbt_ratio,
                    ]
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
