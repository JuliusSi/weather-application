<?php

return [
    'rules' => [
        'max_precipitation' => 0,
        'max_air_temperature' => 26,
        'min_air_temperature' => 14,
        'min_air_temperature_if_clear' => 11,
        'min_air_temperature_if_clear_if_slow_wind' => 8,
        'slow_wind_speed' => 4,
        'hours_to_check' => 4,
        'max_wind_speed' => 8,
        'max_wind_gust' => 12,
    ],
    'available_places' => [
        'vilnius' => 'Vilnius',
    ],
];
