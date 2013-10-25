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
                    'upload-progress' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'       => '/upload-progress',
                            'defaults'    => [
                                'controller' => 'Admin\Controller\GalleryController',
                                'action'     => 'uploadProgress',
                            ],
                        ],
                    ],
                    'adminGallery'  => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/admin-gallery',
                            'defaults' => [
                                'controller' => 'Admin\Controller\AdminController',
                                'action'     => 'adminGallery',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'addAlbum' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/add-album',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'addAlbum',
                                    ],
                                ],
                            ],
                            'editAlbum' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/edit-album/:alias',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'editAlbum',
                                    ],
                                    'constraints' => [
                                        'alias' => '[a-zA-Z0-9][a-zA-Z0-9_-]*',
                                    ],
                                ],
                            ],
                            'uploadAlbumImages' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/upload-album-images/:alias',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'uploadAlbumImages',
                                    ],
                                    'constraints' => [
                                        'alias' => '[a-zA-Z0-9][a-zA-Z0-9_-]*',
                                    ],
                                ],
                            ],
                            'manageAlbumImages' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/manage-album-images/:alias',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'manageAlbumImages',
                                    ],
                                    'constraints' => [
                                        'alias' => '[a-zA-Z0-9][a-zA-Z0-9_-]*',
                                    ],
                                ],
                            ],
                            'manageAlbumImage' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/manage-album-image/:alias',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'manageAlbumImage',
                                    ],
                                    'constraints' => [
                                        'alias' => '[a-zA-Z0-9][a-zA-Z0-9_-]*',
                                    ],
                                ],
                            ],
                            'finishImagesUpload' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/finish-images-upload',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'finishImagesUpload',
                                    ],
                                    'constraints' => [
                                        'alias' => '[a-zA-Z0-9][a-zA-Z0-9_-]*',
                                    ],
                                ],
                            ],
                            'deleteAlbum' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/delete-album/:alias',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'deleteAlbum',
                                    ],
                                    'constraints' => [
                                        'alias' => '[a-zA-Z0-9][a-zA-Z0-9_-]*',
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
