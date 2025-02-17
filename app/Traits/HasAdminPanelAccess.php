<?php

namespace App\Traits;

use Filament\Panel;
use Illuminate\Support\Facades\Auth;

trait HasAdminPanelAccess
{
    public function canAccessPanel(Panel $panel): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        switch ($panel->getId()) {
            case 'app':
                return $user->isActiveAdmin() && $user->hasAdminRole(['staff', 'admin', 'superadmin']);
            case 'user':
                return $user->isActiveTherapist() && $user->hasTherapistRole('therapist');
            default:
                return false;
        }
    }

//    public function canAccessPanel(Panel $panel): bool
//    {
//        $user = Auth::user();
//
//        return $user->admin !== null && $user->isActiveAdmin();
//    }

    public function isActiveAdmin(): bool
    {
        return $this->admin && $this->admin->is_active;
    }

    public function isActiveTherapist(): bool
    {
        return $this->therapist && $this->therapist->is_active;
    }

    public function hasAdminRole(array $roleNames): bool
    {
        return $this->admin && $this->role && in_array($this->role->name, $roleNames, true);
    }

    public function hasTherapistRole(string $roleName): bool
    {
        return $this->therapist && $this->role && $this->role->name === $roleName;
    }
}
