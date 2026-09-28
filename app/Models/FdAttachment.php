<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FdAttachment extends Model
{
    protected $primaryKey = 'fd_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'fd_id',
        'fd_ticket_id',
        'fd_comment_id',
        'name',
        'content_type',
        'size',
        'disk',
        'path',
        'downloaded_at',
        'error',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(FdTicket::class, 'fd_ticket_id', 'fd_id');
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(FdComment::class, 'fd_comment_id', 'fd_id');
    }
}
