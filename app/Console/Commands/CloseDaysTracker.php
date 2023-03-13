<?php

namespace App\Console\Commands;

use App\Models\TrackerProcessing;
use App\Models\User;
use Illuminate\Console\Command;
use App\Models\Tracker;
use Carbon\Carbon;

class CloseDaysTracker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracker:closeDays';

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

        $trackers = Tracker::whereIn('current_status',[1,3])->get();

        foreach($trackers as $tracker){

            if($tracker->current_status == 3) {
                $trackerProcessing = TrackerProcessing::whereStatus(3)
                    ->whereCustomerId($tracker->customer_id)
                    ->whereRaw("date_format(created_at, '%Y-%m-%d') = '" . Carbon::now()->format('Y-m-d') . "'")
                    ->whereNull('action_date_time_stop')
                    ->first();

                if ($trackerProcessing) {
                    $trackerProcessing->update(['action_date_time_stop' => Carbon::now()->format('Y-m-d H:i')]);
                }
            }

            $trackerProcessing =  TrackerProcessing::whereStatus(3)
                ->whereCustomerId($tracker->customer_id)
                ->whereRaw("date_format(created_at, '%Y-%m-%d') = '".Carbon::now()->format('Y-m-d')."'")
                ->get();

            $pausedMinutes = 0;
            foreach ($trackerProcessing as $item) {
                $start = Carbon::parse($item['action_date_time_start']);
                $stop = Carbon::parse($item['action_date_time_stop']);
                $pausedMinutes += $stop->diffInMinutes($start);
            }


            $dateStop = Carbon::now()->format('Y-m-d H:i');
            $start = Carbon::parse($tracker['date_start']);
            $stop = Carbon::parse($dateStop);

            $workTimeMinutes = $stop->diffInMinutes($start) - $pausedMinutes;

            $tracker->update([
                                 'current_status'=>config('statuses.stop_day'),
                                 'date_stop' => $dateStop,
                                 'pause' => $pausedMinutes,
                                 'work' => $workTimeMinutes
                             ]);

        }

    }
}
