<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;

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
        'company_id'
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

    public function company(){
        return $this->belongsTo(Company::class,'company_id');
    }

    public function trackerWorkDays(){
        return $this->hasMany(Tracker::class,'customer_id')->whereCurrentStatus(config('statuses.stop_day'));
    }

    public function trackerSickDays(){
        return $this->hasMany(Tracker::class,'customer_id')->whereCurrentStatus(config('statuses.sick_day'));
    }

    public function trackerVacationDays(){
        return $this->hasMany(Tracker::class,'customer_id')->whereCurrentStatus(config('statuses.vacation_day'));
    }

    public function tracker(){
        return $this->hasMany(Tracker::class,'customer_id');
    }
}
