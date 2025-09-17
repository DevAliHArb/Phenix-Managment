<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{

    use HasFactory;
    use HasApiTokens, Notifiable;

    use SoftDeletes;

    protected $dates = ["deleted_at"];

    const ACTIVE_USER = "true";
    const UNACTIVE_USER = "false";
    const VERIFIED_USER = "1";
    const UNVERIFIED_USER = "0";

    const GOOGLE_USER = "true";
    const REGULAR_USER = "false";

    const BLOCKED_USER = "true";

    const UNBLOCKED_USER = "false";
    const USER_DEFAULT_LANGUAGE = "fr";
    const USER_DEFAULT_CURRENCY = "eur";

    protected $table = 'users';



    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'verified',
        'verification_token',
        'image',
        'active',
        'blocked',
        'google',
        'type',
        'currency',
        'language',
        'token',
        'client_id',
        'company_name',
        'company_address',
        'company_city',
        'siret',
        'tva',
        'delete_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function setfirstNameAttribute($first_name)
    {
        $this->attributes['first_name'] = strtolower($first_name);
    }

    public function getfirstNameAttribute($first_name)
    {
        return ucwords($first_name);
    }
    public function setLastNameAttribute($last_name)
    {
        $this->attributes['last_name'] = strtolower($last_name);
    }

    public function getLastNameAttribute($last_name)
    {
        return ucwords($last_name);
    }

    public function setEmailAttribute($email)
    {
        $this->attributes['email'] = strtolower($email);
    }

    public function setPhoneAttribute($phone)
    {
        $this->attributes['phone'] = strtolower($phone);
    }

    public function setImageAttribute($image)
    {
        $this->attributes['image'] = strtolower($image);
    }

    public function isVerified()
    {
        return $this->verified == User::VERIFIED_USER;
    }

    public function isGoogle()
    {
        return $this->google == User::GOOGLE_USER;
    }


    public function isBlocked()
    {
        return $this->blocked == User::BLOCKED_USER;
    }

    public function isActive()
    {
        return $this->active == User::ACTIVE_USER;
    }
    public static function generateVerificationCode()
    {
        return str_random(40);
    }


    public function userAddresses()
    {
        return $this->hasMany(UserAddress::class);
    }


    public function userPayments()
    {
        return $this->hasMany(UserPayment::class);
    }


    public function userCoupon()
    {
        return $this->hasMany(UserCoupon::class);
    }

    public function userSubscription()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function cart()
    {
        return $this->hasMany(CartItems::class);
    }
    public function favorite()
    {
        return $this->hasMany(Favorite::class);
    }

    public function returnInvoices()
    {
        return $this->hasMany(ReturnInvoice::class);
    }

    public function orderInvoices()
    {
        return $this->hasMany(OrderInvoice::class);
    }

    
    // public function companySettings() { /* CompanySettings reference removed */ }

    public function bookreview()
{
    return $this->hasMany(BookReview::class);
}


public function notifications()
{
    return $this->hasMany(Notify::class);
}

}
