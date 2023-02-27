<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestChangeCompanyInfo extends Model{
    use HasFactory;

    protected $fillable = ['company_id','request_data_update','approved'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'request_change_company_info';

    protected $casts = [
        'request_data_update' => 'array',
    ];

    public function getRequestDataUpdateAttribute($value) {
        return (array)json_decode($value);
    }

    public function company(){
        return $this->hasOne(Company::class,'id','company_id');
    }

    public function scopeNew(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->where('approved', 0);
    }


}
