<?php

namespace App\Http\Middleware;

use App\Models\Platform\PlatformSubscription;
use Closure;
use Illuminate\Http\Request;

class EnforcePlatformPaymentBlock
{
    /**
     * If any active subscription has force_payment enabled and is past grace with
     * unpaid invoices, restrict access to billing pages only.
     *
     * SuperAdmins bypass the block (they need to manage/unblock).
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        if ($user->hasRole('SuperAdmin')) {
            return $next($request);
        }

        $blocking = PlatformSubscription::where('force_payment', true)
            ->whereIn('status', ['Active', 'Suspended'])
            ->get()
            ->first(fn($s) => $s->shouldBlockAccess());

        if (!$blocking) {
            return $next($request);
        }

        // Allowed routes during block
        $allowed = [
            'my.invoices.*',
            'my.services.index',
            'logout',
            'profile.*',
            'public.pay.*', // legacy
        ];

        $routeName = $request->route()?->getName();
        foreach ($allowed as $pattern) {
            if ($routeName && $request->routeIs($pattern)) {
                return $next($request);
            }
        }

        // POST/PUT etc — let through if it's a payment-related action
        if ($routeName && str_starts_with($routeName, 'my.invoices.')) {
            return $next($request);
        }

        return redirect()->route('my.invoices.index')
            ->with('error', 'Access restricted: please settle the overdue invoice for "' . ($blocking->service?->name ?? 'your service') . '" to continue.');
    }
}
