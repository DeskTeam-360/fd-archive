<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FdAgent extends Model
{
    protected $primaryKey = 'fd_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'fd_id',
        'name',
        'email',
        'phone',
        'mobile',
        'job_title',
        'occasional',
        'active',
        'type',
        'group_ids',
        'role_ids',
        'skill_ids',
        'custom_fields',
        'fd_created_at',
        'fd_updated_at',
        'tickets_imported_at',
        'last_synced_at',
    ];

    protected $casts = [
        'occasional'    => 'boolean',
        'active'        => 'boolean',
        'group_ids'     => 'array',
        'role_ids'      => 'array',
        'skill_ids'     => 'array',
        'custom_fields' => 'array',
        'fd_created_at'      => 'datetime',
        'fd_updated_at'      => 'datetime',
        'tickets_imported_at' => 'datetime',
        'last_synced_at'      => 'datetime',
    ];

    public function ticketsAsRequester(): HasMany
    {
        return $this->hasMany(FdTicket::class, 'fd_requester_id', 'fd_id');
    }

    public function ticketsAsResponder(): HasMany
    {
        return $this->hasMany(FdTicket::class, 'fd_responder_id', 'fd_id');
    }
}
