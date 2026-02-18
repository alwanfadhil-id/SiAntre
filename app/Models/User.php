<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, \Spatie\Permission\Traits\HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'google2fa_secret',
        'google2fa_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
            'google2fa_enabled' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include active users (not soft deleted).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope a query to only include admin users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope a query to only include operator users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOperator($query)
    {
        return $query->where('role', 'operator');
    }

    /**
     * Check if the user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is an operator
     */
    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    /**
     * Get the validation rules for creating a user
     */
    public static function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,operator',
        ];
    }

    /**
     * Get the validation rules for updating a user
     */
    public static function getUpdateValidationRules($id): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,operator',
            'password' => 'sometimes|nullable|string|min:8|confirmed',
        ];
    }

    /**
     * Check if two-factor authentication is enabled
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->google2fa_enabled && !empty($this->google2fa_secret);
    }

    /**
     * Generate a secret key for two-factor authentication
     */
    public function generateTwoFactorSecret(): string
    {
        $google2fa = app('pragmarx.google2fa');
        $this->google2fa_secret = $google2fa->generateSecretKey();

        return $this->google2fa_secret;
    }

    /**
     * Get the QR code URL for two-factor authentication
     */
    public function getTwoFactorQrCodeUrl(): string
    {
        $google2fa = app('pragmarx.google2fa');

        return $google2fa->getQRCodeInline(
            config('app.name'),
            $this->email,
            $this->google2fa_secret
        );
    }

    /**
     * Verify the two-factor authentication code
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        $google2fa = app('pragmarx.google2fa');

        return $google2fa->verifyKey($this->google2fa_secret, $code);
    }

    /**
     * Enable two-factor authentication
     */
    public function enableTwoFactor(): void
    {
        $this->google2fa_enabled = true;
        $this->save();
    }

    /**
     * Disable two-factor authentication
     */
    public function disableTwoFactor(): void
    {
        $this->google2fa_enabled = false;
        $this->save();
    }
}
