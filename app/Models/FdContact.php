<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FdContact extends Model
{
    protected $primaryKey = 'fd_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'fd_id',
        'fd_company_id',
        'name',
        'email',
        'phone',
        'mobile',
        'job_title',
        'language',
        'time_zone',
        'custom_fields',
        'fd_created_at',
        'fd_updated_at',
        'tickets_imported_at',
        'last_synced_at',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'fd_created_at' => 'datetime',
        'fd_updated_at' => 'datetime',
        'tickets_imported_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(FdCompany::class, 'fd_company_id', 'fd_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(FdTicket::class, 'fd_requester_id', 'fd_id');
    }
}
