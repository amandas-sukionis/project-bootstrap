<?php
return [
    'doctrine' => [
        'driver'     => [
            'my_annotation_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                'cache' => 'array',
                'paths' => [
                    'module/Application/src/Application/Mapping',
                ],
            ],
            'orm_default'          => [
                'drivers' => [
                    'Application\Entity' => 'my_annotation_driver'
                ]
            ]
        ],
        'connection' => [
            'orm_default' => [
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
            ]
        ]
    ]
];