<?php

namespace App\Policies;

use App\Models\StockTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockTransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any stock transactions.
     */
    public function viewAny($user): bool
    {
        return $user->hasAnyRole('warehouse', 'production', 'director', 'super_admin');
    }

    /**
     * Determine whether the user can view the stock transaction.
     */
    public function view($user, StockTransaction $stockTransaction): bool
    {
        return $user->hasAnyRole('warehouse', 'production', 'director', 'super_admin');
    }

    /**
     * Determine whether the user can create stock transactions.
     */
    public function create($user): bool
    {
        return $user->hasAnyRole('warehouse', 'director', 'super_admin');
    }

    /**
     * Determine whether the user can update the stock transaction.
     */
    public function update($user, StockTransaction $stockTransaction): bool
    {
        return $user->hasAnyRole('warehouse', 'director', 'super_admin');
    }

    /**
     * Determine whether the user can post the stock transaction.
     */
    public function post($user, StockTransaction $stockTransaction): bool
    {
        // Warehouse and director can post; production cannot
        return $user->hasAnyRole('warehouse', 'director', 'super_admin')
            && $stockTransaction->status === 'draft';
    }

    /**
     * Determine whether the user can cancel the stock transaction.
     */
    public function cancel($user, StockTransaction $stockTransaction): bool
    {
        return $user->hasAnyRole('warehouse', 'director', 'super_admin')
            && $stockTransaction->canBeCancelled();
    }

    /**
     * Determine whether the user can approve the stock transaction.
     */
    public function approve($user, StockTransaction $stockTransaction): bool
    {
        return $user->hasAnyRole('director', 'super_admin');
    }

    /**
     * Determine whether the user can perform stock transactions.
     */
    public function perform($user, StockTransaction $stockTransaction): bool
    {
        return $user->hasAnyRole('warehouse', 'director', 'super_admin');
    }
}