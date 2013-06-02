<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bill
 * Date: 31/03/2013
 * Time: 12:38
 * 
 */
namespace Auth\Controller\Plugin;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
class Auth extends AbstractPlugin
{
    public function isAllowed()
    {
        return 'fff';
    }
}
