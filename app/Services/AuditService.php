<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an action in the audit trail.
     *
     * @param string $action Short description of the action
     * @param string|null $details Additional context
     * @return void
     */
    public static function log(string $action, ?string $details = null)
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(), // Nullable if unauthenticated (e.g. login fail, or specific system actions)
                'action' => $action,
                'details' => $details,
                'ip_address' => Request::ip(),
            ]);
        } catch (\Exception $e) {
            // Silently fail to avoid breaking the main app flow if logging fails
            // Log::error('Audit Log Failed: ' . $e->getMessage());
        }
    }
}
