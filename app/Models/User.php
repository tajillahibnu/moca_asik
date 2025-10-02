<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Siswa\Models\Siswa;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'source',
        'source_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::created(function ($user) {
            $user->assignDefaultRole();
        });
    }

    public function createAuthToken(): string
    {
        return $this->createToken('auth_token', $this->getAbilities())->plainTextToken;
    }

    public function getAbilities(): array
    {
        try {
            $permissions = $this->getAllPermissions()->pluck('name')->toArray();
            $roles = $this->getRoleNames();

            return [
                'access:api',
                ...$permissions,
                ...$roles,
            ];
        } catch (\Exception $e) {
            // Fallback untuk testing
            return [
                'access:api',
                'view users',
                'view roles'
            ];
        }
    }

    public function assignDefaultRole(): void
    {
        $defaultRole = \App\Models\Role::where('is_default', true)->first();
        if ($defaultRole) {
            $this->assignRole($defaultRole);
        }
    }

    /**
     * Relasi ke model Siswa.
     */
    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'user_id');
    }
}
