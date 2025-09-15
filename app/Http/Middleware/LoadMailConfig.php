<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Models\CompanySettings;

class LoadMailConfig
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Fetch mail configuration from company_settings table
        $mailConfig = CompanySettings::select('mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption')
            ->first();

        if ($mailConfig) {
            Config::set('mail.mailers.smtp.host', $mailConfig->mail_host);
            Config::set('mail.mailers.smtp.port', $mailConfig->mail_port);
            Config::set('mail.mailers.smtp.username', $mailConfig->mail_username);
            Config::set('mail.mailers.smtp.from_address', $mailConfig->mail_username);
            Config::set('mail.mailers.smtp.password', $mailConfig->mail_password);
            Config::set('mail.mailers.smtp.encryption', $mailConfig->mail_encryption);
        }
        // dd(config('mail.mailers.smtp.username'));
        return $next($request);
    }
}
