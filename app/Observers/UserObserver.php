<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    /**
     * Handle the User "saved" event.
     * Triggered when user is created or updated
     */
    public function saved(User $user): void
    {
        // Clear cache jika email berubah
        if ($user->isDirty('email')) {
            Cache::forget('dev-login-users');
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Cache::forget('dev-login-users');
    }
}
