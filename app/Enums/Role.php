<?php
namespace App\Enums;

enum Role: string {
    case SuperAdmin = 'super_admin';
    case Admin      = 'admin';
    case Staff      = 'staff';
    case User       = 'user';

    public function label(): string {
        return match($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin      => 'Administrator',
            self::Staff      => 'Staff',
            self::User       => 'Visitor',
        };
    }

    public function canAccessAdmin(): bool {
        return in_array($this, [self::SuperAdmin, self::Admin, self::Staff], true);
    }
}
