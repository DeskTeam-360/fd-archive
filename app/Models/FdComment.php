<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FdComment extends Model
{
    protected $primaryKey = 'fd_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'fd_id',
        'fd_ticket_id',
        'fd_user_id',
        'body',
        'body_text',
        'incoming',
        'private',
        'from_email',
        'to_emails',
        'cc_emails',
        'bcc_emails',
        'attachments',
        'fd_created_at',
        'fd_updated_at',
    ];

    protected $casts = [
        'incoming' => 'boolean',
        'private' => 'boolean',
        'to_emails' => 'array',
        'cc_emails' => 'array',
        'bcc_emails' => 'array',
        'attachments' => 'array',
        'fd_created_at' => 'datetime',
        'fd_updated_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(FdTicket::class, 'fd_ticket_id', 'fd_id');
    }
}
