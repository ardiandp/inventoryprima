<?php

namespace App\Policies;

trait HandlesAuthorization
{
    /**
     * Check if user can perform stock transaction based on ownership and role.
     */
    protected function canPerformStockTransaction(User $user, StockTransaction $stockTransaction): bool
    {
        // Warehouse can perform on their own receipts
        if ($user->role->name === 'warehouse') {
            return $stockTransaction->purchase_receipt_id !== null;
        }

        // Director can perform any
        if ($user->role->name === 'director') {
            return true;
        }

        // Super admin can perform any
        if ($user->role->name === 'super_admin') {
            return true;
        }

        return false;
    }
}