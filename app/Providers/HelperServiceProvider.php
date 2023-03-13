<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $helpersFiles = glob(app_path('Helpers').'/*.php');

        foreach ($helpersFiles as $key => $helpersFile) {
            require_once $helpersFile;

        }
    }

}
