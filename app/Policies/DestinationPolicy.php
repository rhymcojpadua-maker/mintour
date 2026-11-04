<?php
namespace App\Policies;

use App\Models\Destination;
use App\Models\User;
use App\Enums\Role;

class DestinationPolicy
{
    public function viewAny(?User $user): bool { return true; }
    public function view(?User $user, Destination $d): bool { return $d->is_published || $user?->isAdmin(); }

    public function create(User $user): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::Admin, Role::Staff], true);
    }

    public function update(User $user, Destination $d): bool
    {
        if ($user->role === Role::SuperAdmin || $user->role === Role::Admin) return true;
        return $user->role === Role::Staff;
    }

    public function delete(User $user, Destination $d): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::Admin], true);
    }
}
