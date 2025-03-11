<?php

namespace App\Traits;

use App\Models\Invoice;
use App\Models\BusinessDebtor;
use Illuminate\Support\Facades\DB;

trait BusinessDebtorSyncing
{
    /**
     * Boot the trait.
     * Register model event hooks for invoices that affect business_debtor relationships.
     */
    public static function bootBusinessDebtorSyncing()
    {
        // When an invoice is created, update the related business_debtor amount
        static::created(function ($invoice) {
            static::syncBusinessDebtorAmount($invoice->business_id, $invoice->debtor_id);
        });

        // When an invoice is updated, update the related business_debtor amount
        static::updated(function ($invoice) {
            if ($invoice->isDirty(['due_amount', 'business_id', 'debtor_id'])) {
                // If the relationship changed, sync both old and new relationships
                if ($invoice->isDirty(['business_id', 'debtor_id'])) {
                    $originalBusinessId = $invoice->getOriginal('business_id');
                    $originalDebtorId = $invoice->getOriginal('debtor_id');
                    
                    if ($originalBusinessId && $originalDebtorId) {
                        static::syncBusinessDebtorAmount($originalBusinessId, $originalDebtorId);
                    }
                }
                
                static::syncBusinessDebtorAmount($invoice->business_id, $invoice->debtor_id);
            }
        });

        // When an invoice is deleted, update the related business_debtor amount
        static::deleted(function ($invoice) {
            static::syncBusinessDebtorAmount($invoice->business_id, $invoice->debtor_id);
        });

        // When an invoice is restored from soft delete, update the related business_debtor amount
        static::restored(function ($invoice) {
            static::syncBusinessDebtorAmount($invoice->business_id, $invoice->debtor_id);
        });
    }

    /**
     * Sync the amount_owed for a business_debtor pair with the sum of related invoices.
     * Also removes business_debtor records that have no invoices.
     *
     * @param int $businessId
     * @param int $debtorId
     * @return void
     */
    public static function syncBusinessDebtorAmount($businessId, $debtorId)
    {
        // Calculate the total due amount for this business-debtor pair
        $totalDueAmount = Invoice::where('business_id', $businessId)
            ->where('debtor_id', $debtorId)
            ->sum('due_amount');

        // If there are no invoices or the total is zero, remove the business_debtor record
        if ($totalDueAmount <= 0) {
            DB::table('business_debtor')
                ->where('business_id', $businessId)
                ->where('debtor_id', $debtorId)
                ->delete();
            return;
        }

        // Otherwise, update or create the business_debtor record
        DB::table('business_debtor')
            ->updateOrInsert(
                [
                    'business_id' => $businessId,
                    'debtor_id' => $debtorId,
                ],
                [
                    'amount_owed' => $totalDueAmount,
                    'updated_at' => now(),
                ]
            );
    }

    /**
     * Command to resync all business_debtor records with their invoices.
     * Useful for maintenance or fixing data inconsistencies.
     *
     * @return array Statistics about the sync operation
     */
    public static function resyncAllBusinessDebtorAmounts()
    {
        $stats = [
            'updated' => 0,
            'created' => 0,
            'deleted' => 0
        ];

        // Get all unique business-debtor pairs from invoices
        $relationships = Invoice::select('business_id', 'debtor_id')
            ->groupBy('business_id', 'debtor_id')
            ->get();

        // Update each relationship
        foreach ($relationships as $rel) {
            $totalDueAmount = Invoice::where('business_id', $rel->business_id)
                ->where('debtor_id', $rel->debtor_id)
                ->sum('due_amount');

            $existing = DB::table('business_debtor')
                ->where('business_id', $rel->business_id)
                ->where('debtor_id', $rel->debtor_id)
                ->first();

            if ($totalDueAmount > 0) {
                DB::table('business_debtor')
                    ->updateOrInsert(
                        [
                            'business_id' => $rel->business_id,
                            'debtor_id' => $rel->debtor_id,
                        ],
                        [
                            'amount_owed' => $totalDueAmount,
                            'updated_at' => now(),
                        ]
                    );
                
                $stats[$existing ? 'updated' : 'created']++;
            } elseif ($existing) {
                DB::table('business_debtor')
                    ->where('business_id', $rel->business_id)
                    ->where('debtor_id', $rel->debtor_id)
                    ->delete();
                
                $stats['deleted']++;
            }
        }

        // Delete any business_debtor records that don't have invoices
        $orphanedCount = DB::table('business_debtor')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('invoices')
                    ->whereRaw('invoices.business_id = business_debtor.business_id')
                    ->whereRaw('invoices.debtor_id = business_debtor.debtor_id');
            })
            ->delete();
        
        $stats['deleted'] += $orphanedCount;

        return $stats;
    }
}