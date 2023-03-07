<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Models\Tracker;

class GenerateTrackerRows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracker:generateRows';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $customers = User::customers()->pluck('id')->toArray();
        foreach($customers as $customerId){
            $dataInsert = [
                'current_status'=>0,
                'customer_id' => $customerId
            ];
            Tracker::create($dataInsert);
        }

    }
}
