<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;


/**
 * 
 *
 * @property-read \App\Models\Tenant|null $tenant
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|TenantUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantUser query()
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class TenantUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tenant_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): Relation
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function tenant(): Relation
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
