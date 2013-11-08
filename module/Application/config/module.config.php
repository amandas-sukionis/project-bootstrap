<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return [
    'router'          => [
        'routes' => [
            'home' => [
                'type'          => 'Zend\Mvc\Router\Http\Literal',
                'options'       => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'createAdmin' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => 'createAdmin',
                            'defaults' => [
                                'controller' => 'Application\Controller\Login',
                                'action'     => 'createAdminUserFromConfig',
                            ],
                        ],
                    ],
                    'logout'      => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => 'logout',
                            'defaults' => [
                                'controller' => 'Application\Controller\Login',
                                'action'     => 'logout',
                            ],
                        ],
                    ],
                    'gallery'     => [
                        'type'          => 'Literal',
                        'options'       => [
                            'route'    => 'gallery',
                            'defaults' => [
                                'controller' => 'Application\Controller\Gallery',
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'album' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/album/:alias',
                                    'defaults'    => [
                                        'controller' => 'Application\Controller\Gallery',
                                        'action'     => 'album',
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
    'service_manager' => [
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ],
        'invokables'         => [
            'Application\Form\UploadImageForm'   => 'Application\Form\UploadImageForm',
            'Application\Form\LoginForm'         => 'Application\Form\LoginForm',
            'Application\Form\AlbumForm'         => 'Application\Form\AlbumForm',
            'Application\Model\GalleryModel'     => 'Application\Model\GalleryModel',
            'Application\Model\UserModel'        => 'Application\Model\UserModel',
        ],
        'aliases'            => [
            'translator' => 'MvcTranslator',
        ],
    ],
    'translator'      => [
        'locale'                    => 'en_US',
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
    'controllers'     => [
        'invokables' => [
            'Application\Controller\Index'   => 'Application\Controller\IndexController',
            'Application\Controller\Login'   => 'Application\Controller\LoginController',
            'Application\Controller\Gallery' => 'Application\Controller\GalleryController'
        ],
    ],
    'view_manager'    => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map'             => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack'      => [
            __DIR__ . '/../view',
        ],
        'strategies'               => array(
            'ViewJsonStrategy',
        ),
    ],
    // Placeholder for console routes
    'console'         => [
        'router' => [
            'routes' => array(),
        ],
    ],
    'doctrine'        => [
        'authentication' => [
            'orm_default' => [
                'object_manager'      => 'Doctrine\ORM\EntityManager',
                'identity_class'      => 'Application\Entity\User',
                'identity_property'   => 'userName',
                'credential_property' => 'password',
                'credentialCallable'  => function ($identity, $credential) {
                    return \Application\Model\UserModel::getPasswordHash($credential, $identity->getSalt());
                }
            ],
        ],
    ],
];
