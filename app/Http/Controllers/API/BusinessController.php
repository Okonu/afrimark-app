<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessController extends Controller
{
    /**
     * List all businesses with pagination
     */
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 15;
        $businesses = Business::with('users')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $businesses,
            'pagination' => [
                'total' => $businesses->total(),
                'per_page' => $businesses->perPage(),
                'current_page' => $businesses->currentPage(),
                'last_page' => $businesses->lastPage()
            ]
        ]);
    }

    /**
     * Get a business by KRA PIN with related debtors and users
     */
    public function show($kra_pin)
    {
        $business = Business::where('registration_number', $kra_pin)->first();

        if (!$business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found with KRA PIN: ' . $kra_pin
            ], 404);
        }

        $business->load(['users:id,name,email']);

        $debtors = DB::table('business_debtor')
            ->where('business_id', $business->id)
            ->join('debtors', 'debtors.id', '=', 'business_debtor.debtor_id')
            ->select([
                'debtors.id',
                'debtors.name',
                'debtors.kra_pin',
                'debtors.email',
                'debtors.status',
                'business_debtor.amount_owed',
                'business_debtor.average_payment_terms',
                'business_debtor.median_payment_terms',
                'business_debtor.average_days_overdue',
                'business_debtor.median_days_overdue',
                'business_debtor.average_dbt_ratio',
                'business_debtor.median_dbt_ratio'
            ])
            ->get();

        $totalDebtorsCount = $debtors->count();
        $totalAmountOwed = $debtors->sum('amount_owed');

        $invoicesSummary = DB::table('invoices')
            ->where('business_id', $business->id)
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(invoice_amount) as total_amount,
                SUM(due_amount) as total_due,
                COUNT(CASE WHEN due_date < NOW() THEN 1 END) as overdue_count,
                AVG(payment_terms) as avg_payment_terms,
                AVG(days_overdue) as avg_days_overdue,
                AVG(dbt_ratio) as avg_dbt_ratio
            ')
            ->first();

        $isAlsoDebtor = DB::table('debtors')
            ->where('kra_pin', $business->registration_number)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'business' => $business,
                'debtors' => $debtors,
                'summary' => [
                    'total_debtors' => $totalDebtorsCount,
                    'total_amount_owed' => $totalAmountOwed,
                    'invoice_summary' => $invoicesSummary,
                    'is_also_debtor' => $isAlsoDebtor
                ]
            ]
        ]);
    }

    /**
     * Search for businesses by name or KRA PIN
     */
    public function search(Request $request)
    {
        $validator = $request->validate([
            'query' => 'required|string|min:3'
        ]);

        $query = $request->query;

        $businesses = Business::where('name', 'LIKE', "%{$query}%")
            ->orWhere('registration_number', 'LIKE', "%{$query}%")
            ->with('users:id,name,email')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $businesses
        ]);
    }
}
