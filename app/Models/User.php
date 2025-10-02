<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Stripe\Stripe;
use Stripe\Account;
use Stripe\AccountLink;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class, 'user_id');
    }

    // -------------------------------
    // Stripe Connect Helpers
    // -------------------------------

    public function getStripeAccountId(): ?string
    {
        return $this->stripe_account_id;
    }

    public function isStripeAccountActive(): bool
    {
        return (bool)$this->stripe_account_active;
    }

    public function createStripeAccount(array $params = []): void
    {
        Stripe::setApiKey(config('app.stripe_secret_key'));

        $account = Account::create(array_merge([
            'type' => 'express',
            'country' => 'US',
            'email' => $this->email,
        ], $params));

        $this->stripe_account_id = $account->id;
        $this->stripe_account_active = false;
        $this->save();
    }

    public function getStripeAccountLink(): string
    {
        Stripe::setApiKey(config('app.stripe_secret_key'));

        $link = AccountLink::create([
            'account' => $this->stripe_account_id,
            'refresh_url' => route('stripe.connect'),
            'return_url' => route('dashboard'),
            'type' => 'account_onboarding',
        ]);

        return $link->url;
    }
}
