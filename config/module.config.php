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
            'Auth\Controller\Error' => 'Auth\Controller\ErrorController',
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
        'aliases' => array(
            'BoosisAuthAdapter' => 'Auth\Adapter\Mongo',
            'BoosisAuthService' => 'AuthService',
        ),
        'factories' => array(
            'Auth\Adapter\Mongo' => function ($sm) {
                $dbAdapter = $sm->get('mongodb');
                $authAdapter = new \Auth\Adapter\Mongo($dbAdapter, 'users', 'username', 'password', null);
                return $authAdapter;
            },
            'BoosisAuthService' => function ($sm) {
                $authAdapter = $sm->get('BoosisAuthAdapter');
                $authService = new \Zend\Authentication\AuthenticationService();
                $authService->setAdapter($authAdapter);
                $authService->setStorage(new \Zend\Authentication\Storage\Session('Auth'));
                return $authService;
            },
        ),
    ),
);
