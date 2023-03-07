<?php

if(!function_exists('convertMinutesToHumanTime')){
    /**
     * convert minutes to hyman time
     * @param int $minutes
     * @return string 00:00
     */
    function convertMinutesToHumanTime($minutes = 0 ): string
    {
        return sprintf('%02d:%02d', $minutes / 60, floor($minutes % 60));
    }
}
