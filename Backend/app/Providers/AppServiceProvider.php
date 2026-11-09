<?php

namespace App\Providers;

use App\Support\Phone;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimits();
    }

    /**
     * Rate limits protect the public forms (no account needed) against spam and fake alerts.
     * Use them on a route with ->middleware('throttle:name').
     */
    private function configureRateLimits(): void
    {
        $limits = config('safeme.rate_limits');

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute($limits['logins_per_minute'])
            ->by('login:' . $request->ip()));

        RateLimiter::for('public-forms', fn (Request $request) => Limit::perHour($limits['forms_per_ip_per_hour'])
            ->by('forms:' . $request->ip()));

        // Missing child reports: limited by IP (one machine) AND by phone (one person on many networks).
        RateLimiter::for('reports', function (Request $request) use ($limits) {
            $result = [Limit::perHour($limits['reports_per_ip_per_hour'])->by('reports-ip:' . $request->ip())];

            $phone = Phone::normalize($request->input('reporter_phone'));
            if ($phone) {
                $result[] = Limit::perDay($limits['reports_per_phone_per_day'])
                    ->by('reports-phone:' . Phone::fingerprint($phone));
            }

            return $result;
        });
    }
}
