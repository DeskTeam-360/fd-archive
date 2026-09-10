<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FdTicket extends Model
{
    protected $primaryKey = 'fd_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'fd_id',
        'fd_company_id',
        'fd_requester_id',
        'fd_responder_id',
        'subject',
        'description',
        'description_text',
        'status',
        'status_label',
        'priority',
        'priority_label',
        'type',
        'source',
        'tags',
        'custom_fields',
        'due_by',
        'fr_due_by',
        'fd_created_at',
        'fd_updated_at',
        'comments_imported_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'custom_fields' => 'array',
        'due_by' => 'datetime',
        'fr_due_by' => 'datetime',
        'fd_created_at'          => 'datetime',
        'fd_updated_at'          => 'datetime',
        'comments_imported_at'   => 'datetime',
    ];

    const STATUS_MAP = [
        2  => 'Open',
        3  => 'Pending',
        4  => 'Resolved',
        5  => 'Closed',
        6  => 'Waiting on Customer',
        7  => 'Waiting on Third Party',
        8  => 'Received',
        9  => 'Question',
        10 => 'Working Team',
        11 => 'AM Review',
        12 => 'Client Review',
        13 => 'Feedback Received',
        14 => 'Revision',
    ];

    const PRIORITY_MAP = [
        1 => 'Low',
        2 => 'Medium',
        3 => 'High',
        4 => 'Urgent',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(FdCompany::class, 'fd_company_id', 'fd_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(FdContact::class, 'fd_requester_id', 'fd_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(FdComment::class, 'fd_ticket_id', 'fd_id');
    }

    public function requesterAgent(): BelongsTo
    {
        return $this->belongsTo(FdAgent::class, 'fd_requester_id', 'fd_id');
    }

    public function responderAgent(): BelongsTo
    {
        return $this->belongsTo(FdAgent::class, 'fd_responder_id', 'fd_id');
    }
}
