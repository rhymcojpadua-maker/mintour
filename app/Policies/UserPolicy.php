<?php
namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool { return $actor->role->value === 'super_admin'; }
    public function create(User $actor): bool  { return $actor->role->value === 'super_admin'; }
    public function update(User $actor, User $target): bool
    {
        return $actor->role->value === 'super_admin';
    }
    public function delete(User $actor, User $target): bool
    {
        return $actor->role->value === 'super_admin' && $actor->id !== $target->id;
    }
}
