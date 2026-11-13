<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Messaging, disputes, audit — spec S13, §12.
 */
class Conversation extends Model
{
    protected $fillable = ['user_one_id', 'user_two_id', 'context_type', 'context_id'];

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }
}

class Message extends Model
{
    protected $fillable = ['conversation_id', 'sender_id', 'body', 'is_ai_assisted', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime', 'is_ai_assisted' => 'boolean'];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

class NotificationPreference extends Model
{
    protected $fillable = ['user_id', 'in_app', 'whatsapp', 'sms', 'email'];

    protected function casts(): array
    {
        return ['in_app' => 'boolean', 'whatsapp' => 'boolean', 'sms' => 'boolean', 'email' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

class Dispute extends Model
{
    protected $fillable = [
        'subject_type', 'subject_id', 'opened_by', 'reason',
        'status', 'resolution', 'resolved_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }
}

class AuditEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['actor_id', 'action', 'entity_type', 'entity_id', 'meta', 'ip_hash', 'created_at'];

    protected function casts(): array
    {
        return ['meta' => 'array', 'created_at' => 'datetime'];
    }
}
