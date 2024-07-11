<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * 
 *
 * @property string $id
 * @property string $email
 * @property string $tenant_id
 * @property string|null $email_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|TenantUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantUser whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantUser whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TenantUser whereUpdatedAt($value)
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class TenantUser extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, MustVerifyEmailTrait, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'tenant_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function tenant(): Relation
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }

    /**
     * @return string
     */
    public function sendEmailVerificationNotification(): string
    {
        $signedUrl = URL::temporarySignedRoute(
            'center.auth.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id'   => $this->getKey(),
                'hash' => implode('_', [sha1($this->tenant_id), sha1($this->getEmailForVerification())]),
            ],
        );

        $frontendUrl      = env('FRONTEND_URL');
        $signedUrlEncoded = urlencode($signedUrl);

        $url = "{$frontendUrl}/accept-invitation?u={$signedUrlEncoded}";

        $this->notify(new VerifyEmailNotification($url));

        return $signedUrl;
    }
}
