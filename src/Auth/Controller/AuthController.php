<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bill
 * Date: 31/03/2013
 * Time: 22:49
 * 
 */
namespace Auth\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class AuthController extends AbstractActionController
{
    /** @var \Zend\Authentication\AuthenticationService */
    protected $authservice;
    public function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        return $this->authservice;
    }
    public function indexAction()
    {
        $messages = array();
        $session = new Container('APP');
        $redirect = $this->params()->fromQuery('_redirect');
        if ($redirect) {
            $session->_redirect = $redirect;
        }
        if ($this->getRequest()->isPost()) {
            $username = $this->getRequest()->getPost()->username;
            $password = $this->getRequest()->getPost()->password;
            $this->getAuthService()
                ->getAdapter()
                ->setIdentity($username)
                ->setCredential($password);
            /** @var $result \Zend\Authentication\Result */
            $result = $this->getAuthService()->authenticate();
            if (!$result->isValid()) {
                $messages = $result->getMessages();
            } else {
                $messages = array('Success');
                /** @var $storage \Zend\Authentication\Storage\Session */
                $storage = $this->getAuthService()->getStorage();
                $storage->write($result->getIdentity());
                if (!empty($session->_redirect)) {
                    $this->redirect()->toUrl($session->_redirect);
                }
            }
        }
        return new ViewModel(array(
            'messages' => $messages
        ));
    }
}