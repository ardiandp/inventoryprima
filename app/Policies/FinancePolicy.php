<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Invoice;
use Illuminate\Auth\Access\HandlesAuthorization;

class FinancePolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->hasAnyRole('finance', 'director', 'super_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole('finance', 'director', 'super_admin');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyRole('finance', 'director', 'super_admin');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('super_admin');
    }

    public function pay(User $user, Invoice $invoice): bool
    {
        // Finance can pay, director can also pay, super_admin can pay
        // But finance cannot modify stock
        return $user->hasAnyRole('finance', 'director', 'super_admin')
            && $invoice->status !== 'paid';
    }

    public function refund(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('super_admin');
    }
}