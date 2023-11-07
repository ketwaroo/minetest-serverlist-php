<?php

use Ketwaroo\MinetestServerList\Storage\PlainJsonStorage;

return [
    "ALLOW_UPDATE_WITHOUT_OLD" => true,
    "PURGE_TIME"               => 350,
    'storage'                  => PlainJsonStorage::class,
    'storage_config'           => [
        PlainJsonStorage::class => [
            'dataFile' => __DIR__ . '/../data/list.json',
        ]
    ],
];
