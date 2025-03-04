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
