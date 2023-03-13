<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class Tracker extends Model{
    use HasFactory;

    protected $fillable = ['customer_id','current_status','comments','date_start','date_stop','pause','work'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tracker';

    public function customer(){
        return $this->hasOne(User::class,'id','customer_id');
    }

    protected $dates = [
        'date_start',
        'date_stop',
    ];

}
