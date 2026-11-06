<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;

class AuditLoginListener
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        AuditLog::create([
            'actor_id' => $user->id,
            'actor_role' => $user->role ?? 'unknown',
            'action' => 'User Login',
            'action_type' => 'LOGIN',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => ['email' => $user->email],
        ]);
    }
}
