<?php

namespace App\Jobs;

use App\Models\Debtor;
use App\Notifications\DebtorListingNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendDebtorNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [30, 60, 120];

    /**
     * The debtor to notify.
     *
     * @var \App\Models\Debtor
     */
    protected $debtor;

    /**
     * Flag to skip immediate sending
     *
     * @var bool
     */
    protected $skipImmediate;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Debtor $debtor
     * @param bool $skipImmediate Whether to skip immediate sending
     */
    public function __construct(Debtor $debtor, bool $skipImmediate = false)
    {
        $this->debtor = $debtor;
        $this->skipImmediate = $skipImmediate;
        $this->onQueue('notifications');
    }

    /**
     * Dispatch the job with immediate notification followed by queued retries.
     *
     * @param \App\Models\Debtor $debtor
     * @return void
     */
    public static function dispatchWithImmediate(Debtor $debtor): void
    {
        try {
            // Load business relationship if not already loaded
            if (!$debtor->relationLoaded('businesses')) {
                $debtor->load('businesses');
            }

            // Ensure verification token exists
            if (!$debtor->verification_token) {
                $debtor->verification_token = Str::random(64);
                $debtor->save();
            }

            // Send notification immediately (synchronously)
            $debtor->notify(new DebtorListingNotification($debtor));

            Log::info("Immediate notification sent to debtor: {$debtor->id} at {$debtor->email}");

            // Queue a backup job for retry in case the email is delayed or fails to deliver
            static::dispatch($debtor, true)->onQueue('notifications');
        } catch (\Exception $e) {
            Log::error("Failed to send immediate notification: " . $e->getMessage());

            // Queue the job for retry if immediate send fails
            static::dispatch($debtor, false)->onQueue('notifications');
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Skip if we already sent it immediately and this is the first attempt
        if ($this->skipImmediate && $this->attempts() === 1) {
            Log::info("Skipping notification for debtor {$this->debtor->id} - already sent immediately");
            return;
        }

        // Reload the model to ensure we have the latest data
        $this->debtor = Debtor::find($this->debtor->id);

        if (!$this->debtor) {
            Log::error("Debtor not found when sending notification: {$this->debtor->id}");
            return;
        }

        try {
            // Load business relationship
            if (!$this->debtor->relationLoaded('businesses')) {
                $this->debtor->load('businesses');
            }

            // Ensure verification token exists
            if (!$this->debtor->verification_token) {
                $this->debtor->verification_token = Str::random(64);
                $this->debtor->save();
            }

            // Send using the existing notification class
            $this->debtor->notify(new DebtorListingNotification($this->debtor));

            Log::info("Notification sent to debtor: {$this->debtor->id} at {$this->debtor->email} (attempt {$this->attempts()})");

        } catch (\Exception $e) {
            Log::error("Failed to send notification to debtor {$this->debtor->id}: " . $e->getMessage());

            // If it's the last attempt, just log the failure
            if ($this->attempts() >= $this->tries) {
                Log::error("All attempts to send notification failed for debtor {$this->debtor->id}");
            } else {
                // Otherwise rethrow to allow retry
                throw $e;
            }
        }
    }
}
