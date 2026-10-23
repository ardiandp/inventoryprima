<?php

namespace App\Policies;

use App\Models\User;
use App\Models\QcInspection;
use Illuminate\Auth\Access\HandlesAuthorization;

class QcPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->hasAnyRole('qc', 'director', 'super_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole('qc', 'director', 'super_admin');
    }

    public function update(User $user, QcInspection $inspection): bool
    {
        return $user->hasAnyRole('qc', 'director', 'super_admin');
    }

    public function delete(User $user, QcInspection $inspection): bool
    {
        return $user->hasRole('super_admin');
    }

    public function accept(User $user, QcInspection $inspection): bool
    {
        return $user->hasAnyRole('qc', 'director', 'super_admin');
    }

    public function reject(User $user, QcInspection $inspection): bool
    {
        return $user->hasAnyRole('qc', 'director', 'super_admin');
    }

    public function post(User $user, QcInspection $inspection): bool
    {
        return $user->hasRole('super_admin');
    }
}