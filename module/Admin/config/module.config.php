<?php
return [
    'router'       => [
        'routes' => [
            'admin' => [
                'type'          => 'Literal',
                'options'       => [
                    'route'    => '/admin',
                    'defaults' => [
                        'controller' => 'Admin\Controller\AdminController',
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'dashboard'  => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/dashboard',
                            'defaults' => [
                                'controller' => 'Admin\Controller\AdminController',
                                'action'     => 'dashboard',
                            ],
                        ],
                    ],
                    'gallery'  => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/gallery',
                            'defaults' => [
                                'controller' => 'Admin\Controller\AdminController',
                                'action'     => 'gallery',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'addCategory' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/addCategory',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'addCategory',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers'  => [
        'invokables' => [
            'Admin\Controller\AdminController' => 'Admin\Controller\AdminController',
            'Admin\Controller\GalleryController' => 'Admin\Controller\GalleryController',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];