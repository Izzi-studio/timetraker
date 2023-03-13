<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'position',
        'owner',
        'company_id',
        'timezone'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'company_id',
        'owner'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    /**
     * Boot
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($query) {
            $query->timezone = request()->hasHeader('time-zone') ? request()->header('time-zone') : env('DEFAULT_TIMEZONE');
        });
    }
    public function company(){
        return $this->belongsTo(Company::class,'company_id');
    }

    public function tracker(){
        return $this->hasMany(Tracker::class,'customer_id');
    }
    public function trackerProcessing(){
        return $this->hasMany(TrackerProcessing::class,'customer_id');
    }

    public function scopeCustomers(\Illuminate\Database\Eloquent\Builder $query){
        return $query->where('owner',0)->where('is_admin',0);
    }

    public function scopeCompany(\Illuminate\Database\Eloquent\Builder $query,$company_id = null){
        return $query->where('company_id',$company_id);
    }
}
