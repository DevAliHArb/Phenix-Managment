<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GuestUsers;
use Carbon\Carbon;

class ClearExpiredEmailVerifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guestusers:clear-expired-email-verifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear email_verified_at if it is older than 1 hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expired = GuestUsers::whereNotNull('email_verified_at')
            ->where('email_verified_at', '<', Carbon::now()->subHour())
            ->update(['email_verified_at' => null]);

        $this->info("Cleared email_verified_at for $expired users.");
    }
}
