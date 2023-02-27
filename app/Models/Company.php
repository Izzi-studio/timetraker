<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model{
    use HasFactory;

    protected $fillable = ['company_name','company_phone','company_address','company_email','company_logo','status','owner_id'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'companies';


    public function customers(){
        return $this->hasMany(User::class,'company_id');
    }

    public function requests(){
        return $this->hasOne(RequestChangeCompanyInfo::class,'company_id');
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->where('status', 1);
    }

    public function scopeNoActive(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->where('status', 0);
    }

    public function scopeSuspended(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->where('status', 2);
    }
}
