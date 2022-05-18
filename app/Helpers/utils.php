<?php 
use Carbon\Carbon;


// formating timestamp attribute
function getTimeStampsAttribute($date) {
        $date = Carbon::parse($date)->locale('id');
        $date->settings(['formatFunction' => 'translatedFormat']);
        return $date->format('l, j F Y ; H:i:s');
        // return Carbon::createFromFormat('Y-m-d H:i:s', $date)->copy()->tz('Asia/Jakarta')->format('F j, Y @ g:i A');
    }
