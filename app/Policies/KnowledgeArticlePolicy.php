<?php
namespace App\Policies;

use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Enums\Role;

class KnowledgeArticlePolicy
{
    public function viewAny(User $user): bool { return $user->role->canAccessAdmin(); }
    public function create(User $user): bool  { return $user->role->canAccessAdmin(); }
    public function update(User $user, KnowledgeArticle $a): bool { return $user->role->canAccessAdmin(); }
    public function delete(User $user, KnowledgeArticle $a): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::Admin], true);
    }
}
