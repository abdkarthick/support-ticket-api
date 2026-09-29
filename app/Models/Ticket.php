<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'description',
        'status',
        'priority',
        'assigned_to',
        'sla_deadline',
        'breached_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'sla_deadline' => 'datetime',
            'breached_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    // Who created ticket (customer)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Agent assigned
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class)->latest();
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }

    public function isBreached(): bool
    {
        return $this->breached_at !== null || 
               ($this->sla_deadline && now()->greaterThan($this->sla_deadline) && !in_array($this->status, ['resolved','closed']));
    }
}