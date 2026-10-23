<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PurchaseOrder;

/**
 * Purchase Order Policy.
 *
 * Handles authorization for Purchase Order actions:
 * - Creating POs from approved RPOs
 - Supplier confirmation
 * - Partial/full receiving
 * - Closing and cancellation
 *
 * Protected: Pengaju tidak boleh menyetujui pengajuan sendiri.
 */
class PurchaseOrderPolicy
{
    /** Menghasilkan PO dari RPO yang sudah diapprove. */
    public function createFromRPO(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Hanya purchasing dan director yang boleh membuat PO dari RPO.
        return $user->hasPermission('purchasing') || $user->hasPermission('director');
    }

    /** Menyetujui PO dari supplier. */
    public function confirmSupplier(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Hanya supplier dan director yang boleh konfirmasi PO.
        return $user->hasPermission('director') || ($user->hasPermission('supplier') && $user->id === $purchaseOrder->supplier_id);
    }

    /** Menolak PO. */
    public function rejectPO(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Hanya director yang boleh reject PO.
        return $user->hasPermission('director');
    }

    /** Menerima parsial PO. */
    public function receivePartial(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Warehouse dan director dapat menerima parsial.
        return $user->hasPermission('warehouse') || $user->hasPermission('director');
    }

    /** Menerima penuh PO. */
    public function receiveFully(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Hanya warehouse dan director yang menerima penuh.
        return $user->hasPermission('warehouse') || $user->hasPermission('director');
    }

    /** Menutup PO. */
    public function closePO(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Hanya director yang bisa menutup PO.
        return $user->hasPermission('director');
    }

    /** Membatalkan PO. */
    public function cancelPO(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Hanya purchasing dan director yang bisa membatalkan.
        return $user->hasPermission('purchasing') || $user->hasPermission('director');
    }
}