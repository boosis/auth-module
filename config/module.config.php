<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'login' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
                        'controller' => 'Auth\Controller\Auth',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Auth\Controller\Auth' => 'Auth\Controller\AuthController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'AuthPlugin' => 'Auth\Controller\Plugin\Auth',
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'AuthService' => function ($sm) {
                $dbAdapter = $sm->get('mongodb');
                $dbTableAuthAdapter = new \Auth\Adapter\Mongo($dbAdapter, 'users', 'username', 'password', null);
                $authService = new \Zend\Authentication\AuthenticationService();
                $authService->setAdapter($dbTableAuthAdapter);
                $authService->setStorage(new \Zend\Authentication\Storage\Session('Auth'));
                return $authService;
            },
        )
    ),
);
