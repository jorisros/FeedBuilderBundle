<?php

return [
    'providers' => [
        'FeedBuilderBundle\Provider\TestProvider'
        ],
    'feeds' => [
        1 =>[
            'title'=>'Testfeed',
            'channel'=>'channel1',
            'ipaddress'=>'127.0.0.1',
            'path'=>'/',
            'published'=>false,
            'class'=>'\\Pimcore\\Model\\DataObject\\Product',
            'root'=>'Products'
        ]
    ]
];