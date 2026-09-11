<?php

return [
    'ip' => env('ZK_IP'),
    'port' => env('ZK_PORT', 4370),
    'timeout' => env('ZK_TIMEOUT', 2),
    'eventTypeStart' => 43,
    'loginMethodStart' => 36,
    'undefinedEvent' => 50 ,
    'undefinedLoginMethod' => 42
    ];
