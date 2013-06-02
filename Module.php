<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Auth;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\GenericResource;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\Session\Container;

class Module
{

    public function onBootstrap(MvcEvent $e)
    {
        $e->getApplication()->getServiceManager()->get('translator');
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $eventManager->attach('route', array($this, 'onRoute'), 2);
    }

    public function onRoute(MvcEvent $e)
    {
        $acl = $this->initAcl($e);
        $application = $e->getApplication();
        $sm = $application->getServiceManager();

        $router = $sm->get('router');
        $request = $sm->get('request');

        /** @var $matchedRoute RouteMatch */
        $matchedRoute = $router->match($request);
        if ($matchedRoute) {
            $namespace = $matchedRoute->getParam('__NAMESPACE__');
            $controller = $matchedRoute->getParam('controller');
            $action = $matchedRoute->getParam('action');
            $resource = $namespace . '\\' . ucwords($controller);
            if (substr($resource, 0, 1) !== '\\') {
                $resource = '\\' . $resource;
            }
            $authService = $sm->get('AuthService');
            if ($authService->hasIdentity()) {
                $role = $authService->getIdentity()['role'];
            } else {
                $role = 'guest';
            }
            if (!$acl->isAllowed($role, $resource, $action)) {
                if ($authService->hasIdentity()) {
                    $url = $e->getRouter()->assemble(array("controller" => 'Auth\Controller\Auth'), array('name' => 'noauth'));
                } else {
                    $url = $e->getRouter()->assemble(array("controller" => 'Auth\Controller\Auth'), array('name' => 'login'));
                }
                $session = new Container('APP');
                $session->_redirect = 'http://www.google.com';
                $response = $e->getResponse();
                $response->setHeaders($response->getHeaders()->addHeaderLine('Location', $url));
                $response->setStatusCode(302);
                $response->sendHeaders();
                exit ();
            }
        }
    }

    private function initAcl(MvcEvent $e)
    {
        $application = $e->getApplication();
        $sm = $application->getServiceManager();
        $config = $sm->get('acl_config');
        $roles = $config['acl']['roles'];
        $resources = $config['acl']['resources'];
        $acl = new Acl();
        foreach ($roles as $role => $parent) {
            $role = new GenericRole($role);
            $acl->addRole($role, $parent);
        }
        if (!empty($resources['allow'])) {
            foreach ($resources['allow'] as $resource => $info) {
                if ($acl->hasResource($resource)) {
                    $resource = $acl->getResource($resource);
                } else {
                    $resource = new GenericResource($resource);
                    $acl->addResource($resource);
                }
                foreach ($info as $role => $privileges) {
                    if ($privileges == array('*')) {
                        $acl->allow($role, $resource);
                    } else {
                        $acl->allow($role, $resource, $privileges);
                    }
                }
            }
        }
        if (!empty($resources['deny'])) {
            foreach ($resources['deny'] as $resource => $info) {
                if ($acl->hasResource($resource)) {
                    $resource = $acl->getResource($resource);
                } else {
                    $resource = new GenericResource($resource);
                    $acl->addResource($resource);
                }
                foreach ($info as $role => $privileges) {
                    if ($privileges == '*') {
                        $acl->deny($role, $resource);
                    } else {
                        $acl->deny($role, $resource, $privileges);
                    }
                }
            }
        }
        $application = $e->getApplication();
        $sm = $application->getServiceManager();
        $sm->setService('acl', $acl);
        return $acl;
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array();
    }
}
