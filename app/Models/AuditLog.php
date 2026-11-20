<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $module, string $action, ?string $details = null, ?int $userId = null): self
    {
        $u = $userId ? User::find($userId) : auth()->user();
        return self::create([
            'user_id' => $u ? $u->id : null,
            'user_name' => $u ? ($u->name ?? $u->username) : 'System',
            'module' => $module,
            'action' => $action,
            'details' => $details,
            'ip_address' => request()->ip(),
        ]);
    }
}
