<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $organization_id
 * @property string $email
 * @property \App\Enums\OrganizationRole $role
 * @property string $token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organization $organization
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationInvitation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationInvitation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationInvitation whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationInvitation whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationInvitation whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationInvitation whereUpdatedAt($value)
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
