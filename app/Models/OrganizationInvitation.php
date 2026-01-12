<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \App\Enums\OrganizationRole $role
 * @property-read \App\Models\Organization|null $organization
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationInvitation query()
 *
 * @mixin \Eloquent
 */
class OrganizationInvitation extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'role' => \App\Enums\OrganizationRole::class,
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
