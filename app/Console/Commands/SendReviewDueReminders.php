<?php

namespace App\Console\Commands;

use App\Mail\DocumentsReviewDue;
use App\Models\Document;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendReviewDueReminders extends Command
{
    protected $signature = 'reminders:review-due';

    protected $description = 'Mengirim email pengingat mingguan ke admin aktif untuk dokumen yang jatuh tempo tinjau ulang';

    public function handle(): int
    {
        $documents = Document::whereNotNull('next_review_at')
            ->whereDate('next_review_at', '<=', today())
            ->with('type')
            ->get();

        if ($documents->isEmpty()) {
            $this->info('Tidak ada dokumen yang jatuh tempo tinjau ulang.');

            return self::SUCCESS;
        }

        $recipients = User::whereIn('role', ['admin', 'super_admin'])
            ->where('is_active', true)
            ->get();

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new DocumentsReviewDue($documents));
            } catch (Throwable $exception) {
                Log::warning('Gagal mengirim email pengingat dokumen jatuh tempo.', [
                    'user_id' => $recipient->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->info("Pengingat {$documents->count()} dokumen jatuh tempo dikirim ke {$recipients->count()} admin aktif.");

        return self::SUCCESS;
    }
}
