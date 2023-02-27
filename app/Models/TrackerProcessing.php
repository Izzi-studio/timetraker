<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackerProcessing extends Model{
    use HasFactory;

    protected $fillable = ['tracker_id','status','customer_id','action_date_time_start','action_date_time_stop'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tracker_processing';


}
