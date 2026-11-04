<?php
namespace App\Models;

use App\Enums\Role;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use HasFactory;
    use Notifiable;

    protected $fillable = ['name','email','password','role','preferred_lang','avatar_path','is_active'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'role'              => Role::class,
        'is_active'         => 'boolean',
    ];

    public function reviews()   { return $this->hasMany(Review::class); }
    public function favorites() { return $this->hasMany(Favorite::class); }
    public function chatSessions() { return $this->hasMany(ChatbotSession::class); }

    public function isAdmin(): bool { return $this->role->canAccessAdmin(); }

    public function scopeActive($q) { return $q->where('is_active', true); }
}
