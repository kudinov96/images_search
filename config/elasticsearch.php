<?php

return [
    'hosts' => [
        env('ELASTICSEARCH_HOST', 'http://localhost:9200'),
    ],
    'password' => env('ELASTICSEARCH_PASSWORD', 'default'),
];
