<?php
namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use App\Enums\Role;

class ReviewPolicy
{
    public function create(User $user): bool { return true; }

    public function update(User $user, Review $r): bool
    {
        return $user->id === $r->user_id
            || in_array($user->role, [Role::SuperAdmin, Role::Admin], true);
    }

    public function delete(User $user, Review $r): bool
    {
        return $this->update($user, $r);
    }
}
