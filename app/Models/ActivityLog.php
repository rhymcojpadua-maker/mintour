<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model {
    public $timestamps = false;
    protected $fillable = ['user_id','action','subject_type','subject_id','meta','ip_address','created_at'];
    protected $casts = ['meta' => 'array', 'created_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }

    public static function record(string $action, $subject = null, array $meta = []): void {
        static::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->id,
            'meta'         => $meta,
            'ip_address'   => request()->ip(),
            'created_at'   => now(),
        ]);
    }
}
