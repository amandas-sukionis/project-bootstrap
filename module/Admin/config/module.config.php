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
                            'userAlbums' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/:userId',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'userAlbums',
                                    ],
                                    'constraints' => [
                                        'userId' => '[0-9]+',
                                    ],
                                ],
                            ],
                            'addAlbum' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/:userId/add-album',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'addAlbum',
                                    ],
                                    'constraints' => [
                                        'userId' => '[0-9]+',
                                    ],
                                ],
                            ],
                            'editAlbum' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/:userId/edit-album/:alias',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'editAlbum',
                                    ],
                                    'constraints' => [
                                        'alias' => '[a-zA-Z0-9][a-zA-Z0-9_-]*',
                                        'userId' => '[0-9]+',
                                    ],
                                ],
                            ],
                            'uploadAlbumImages' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/:userId/upload-album-images/:alias',
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
                                    'route'       => '/:userId/manage-album-images/:alias',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'manageAlbumImages',
                                    ],
                                    'constraints' => [
                                        'alias' => '[a-zA-Z0-9][a-zA-Z0-9_-]*',
                                        'userId' => '[0-9]+',
                                    ],
                                ],
                            ],
                            'manageAlbumImage' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/manage-album-image/:albumAlias/:imageAlias',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'manageAlbumImage',
                                    ],
                                    'constraints' => [
                                        'alias' => '[a-zA-Z0-9][a-zA-Z0-9_-]*',
                                    ],
                                ],
                            ],
                            'finishImageUpload' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/finish-image-upload[/:alias]',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'finishImageUpload',
                                    ],
                                    'constraints' => [
                                        'alias' => '[a-zA-Z0-9][a-zA-Z0-9_-]*',
                                    ],
                                ],
                            ],
                            'deleteAlbum' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/:userId/delete-album/:alias',
                                    'defaults'    => [
                                        'controller' => 'Admin\Controller\GalleryController',
                                        'action'     => 'deleteAlbum',
                                    ],
                                    'constraints' => [
                                        'alias' => '[a-zA-Z0-9][a-zA-Z0-9_-]*',
                                        'userId' => '[0-9]+',
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
