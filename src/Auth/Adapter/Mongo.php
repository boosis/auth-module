<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bill
 * Date: 31/03/2013
 * Time: 22:33
 * 
 */
namespace Auth\Adapter;

use Zend\Authentication\Adapter\AbstractAdapter;
use Zend\Authentication\Adapter\Exception\RuntimeException;
use Zend\Authentication\Result;

class Mongo extends AbstractAdapter
{
    /** @var \MongoDB */
    protected $mongoDb;
    protected $collectionName = null;
    protected $identityField = null;
    protected $credentialField = null;
    protected $credentialTreatment = null;
    public function __construct(\MongoDB $mongoDb, $collectionName = null, $identityField = null, $credentialField = null, $credentialTreatment = null)
    {
        $this->mongoDb = $mongoDb;
        $this->collectionName = $collectionName;
        $this->identityField = $identityField;
        $this->credentialField = $credentialField;
        $this->credentialTreatment = $credentialTreatment;
    }
    protected function authenticateSetup()
    {
        $exception = null;

        if ($this->collectionName == '') {
            $exception = 'A collection name must be supplied for the Mongo authentication adapter.';
        } elseif ($this->identityField == '') {
            $exception = 'An identity field must be supplied for the Mongo authentication adapter.';
        } elseif ($this->credentialField == '') {
            $exception = 'A credential field must be supplied for the Mongo authentication adapter.';
        } elseif ($this->identity == '') {
            $exception = 'A value for the identity was not provided prior to authentication with Mongo.';
        } elseif ($this->credential === null) {
            $exception = 'A credential value was not provided prior to authentication with Mongo.';
        }

        if (null !== $exception) {
            throw new RuntimeException($exception);
        }

        return true;
    }

    /**
     * Performs an authentication attempt
     *
     * @return Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        $this->authenticateSetup();

        switch (strtolower($this->credentialTreatment)) {
            case 'md5':
                $credential = md5($this->credential);
                break;
            case 'sha1':
                $credential = sha1($this->credential);
                break;
            default:
                $credential = $this->credential;
        }

        $result = $this->mongoDb->selectCollection($this->collectionName)->findOne(array(
            $this->identityField => $this->identity,
            $this->credentialField => $credential
        ));

        if ($result) {
            $authResult = new Result(Result::SUCCESS, $result);
        } else {
            $authResult = new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null, array('A record with the supplied identity could not be found.'));
        }
        return $authResult;
    }
}
