<?php

namespace App\Filament\Client\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\Debtor;
use App\Models\Invoice;
use App\Services\Business\OnboardingService;
use Illuminate\Support\Facades\DB;

class Homepage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.client.pages.dashboard';

    public ?Business $business = null;
    public array $analytics = [];
    public array $progress = [];
    public ?string $nextStepUrl = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->business = $user ? $user->businesses()->first() : null;

        // Load the analytics data
        $this->loadAnalytics();

        // Load the onboarding progress
        $this->loadProgress();
    }

    protected function loadAnalytics(): void
    {
        if (!$this->business) {
            $this->analytics = [
                'total_debtors' => 0,
                'total_outstanding' => 0,
                'current_amount' => 0,
                'current_invoices' => 0,
                'overdue_amount' => 0,
                'overdue_invoices' => 0,
                'by_age' => [],
                'by_status' => [],
            ];
            return;
        }

        // Total debtors
        $totalDebtors = DB::table('business_debtor')
            ->where('business_id', $this->business->id)
            ->count();

        // Total outstanding
        $totalOutstanding = DB::table('business_debtor')
            ->where('business_id', $this->business->id)
            ->sum('amount_owed');

        // Current invoices
        $currentQuery = Invoice::where('business_id', $this->business->id)
            ->where('due_amount', '>', 0)
            ->where('days_overdue', '<=', 0);

        $currentAmount = $currentQuery->sum('due_amount');
        $currentInvoices = $currentQuery->count();

        // Overdue invoices
        $overdueQuery = Invoice::where('business_id', $this->business->id)
            ->where('due_amount', '>', 0)
            ->where('days_overdue', '>', 0);

        $overdueAmount = $overdueQuery->sum('due_amount');
        $overdueInvoices = $overdueQuery->count();

        // Aging analysis
        $ageRanges = [
            'current' => [0, 0],
            '1-30' => [1, 30],
            '31-60' => [31, 60],
            '61-90' => [61, 90],
            '90+' => [91, 999]
        ];

        $byAge = [];
        foreach ($ageRanges as $label => $range) {
            $query = Invoice::where('business_id', $this->business->id)
                ->where('due_amount', '>', 0);

            if ($label === 'current') {
                $query->where('days_overdue', '<=', 0);
            } else {
                $query->whereBetween('days_overdue', $range);
            }

            $amount = $query->sum('due_amount');
            $count = $query->count();

            // Calculate percentage
            $percentage = $totalOutstanding > 0 ? ($amount / $totalOutstanding) * 100 : 0;

            $byAge[$label] = [
                'amount' => $amount,
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        // Status breakdown
        $statuses = ['active', 'disputed', 'pending', 'paid'];
        $byStatus = [];

        foreach ($statuses as $status) {
            $query = DB::table('business_debtor as bd')
                ->join('debtors as d', 'bd.debtor_id', '=', 'd.id')
                ->where('bd.business_id', $this->business->id)
                ->where('d.status', $status);

            $count = $query->count();
            $amount = $query->sum('bd.amount_owed');

            $byStatus[$status] = [
                'count' => $count,
                'amount' => $amount
            ];
        }

        $this->analytics = [
            'total_debtors' => $totalDebtors,
            'total_outstanding' => $totalOutstanding,
            'current_amount' => $currentAmount,
            'current_invoices' => $currentInvoices,
            'overdue_amount' => $overdueAmount,
            'overdue_invoices' => $overdueInvoices,
            'by_age' => $byAge,
            'by_status' => $byStatus,
        ];
    }

    protected function loadProgress(): void
    {
        if (!$this->business) {
            $this->progress = [
                'percentage' => 0,
                'steps' => [
                    'business_info' => false,
                    'email_verified' => false,
                    'documents_uploaded' => false,
                    'debtors_added' => false,
                ],
            ];
            $this->nextStepUrl = route('filament.client.auth.business-information');
            return;
        }

        $onboardingService = app(OnboardingService::class);
        $this->progress = $onboardingService->getBusinessProgress($this->business);

        if (isset($this->progress['next_step'])) {
            $this->nextStepUrl = $onboardingService->getNextStepUrl($this->progress['next_step']);
        } else {
            $this->nextStepUrl = null;
        }
    }
}
