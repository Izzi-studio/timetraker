<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class Tracker extends Model{
    use HasFactory;

    protected $fillable = ['customer_id','current_status','comments','date_start','date_stop','pause','total_work'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tracker';

    public function scopeAllWorkTimeCurrentMonth(\Illuminate\Database\Eloquent\Builder $query){
        return $query->whereRaw("date_format(created_at, '%Y-%m') = '".Carbon::now()
                      ->format('Y-m')."'")->sum('total_work');
    }

}
