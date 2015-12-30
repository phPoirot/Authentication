<?php

namespace Poirot\AuthSystem\Authenticate;

use Poirot\AuthSystem\Authenticate\Exceptions\AuthenticationException;
use Poirot\AuthSystem\Authenticate\Interfaces\iAuthenticator;
use Poirot\AuthSystem\Authenticate\Interfaces\iCredential;
use Poirot\AuthSystem\Authenticate\Interfaces\iIdentifier;
use Poirot\AuthSystem\Authenticate\Interfaces\iIdentity;
use Poirot\Core\AbstractOptions;
use Poirot\Core\BuilderSetterTrait;
use Poirot\Core\Interfaces\iDataSetConveyor;

abstract class AbstractAuthenticator implements iAuthenticator
{
    use BuilderSetterTrait;

    /** @var iCredential */
    protected $credential;

    /** @var iIdentifier */
    protected $identifier;

    // options:
    /** @var iIdentifier */
    protected $default_identifier;

    /**
     * Authenticate
     *
     * - authenticate user using credential
     * - login into identifier with iIdentity set from recognized
     *   user data
     *
     * note: after successful authentication, you must call
     *       login() outside of method to store identified user
     *
     * @param iCredential|iDataSetConveyor|array $credential
     *
     * @throws AuthenticationException Or extend of this
     * @return iIdentifier
     */
    function authenticate($credential = null)
    {
        if ($credential !== null)
            $this->credential()->from($credential);

        $identity = $this->doAuthenticate();
        if (!$identity instanceof iIdentity)
            throw new AuthenticationException('user authentication failure');

        $this->identifier()->setIdentity($identity);
        return $this->identifier();
    }

    /**
     * Authenticate user with Credential Data and return
     * FullFilled Identity Instance
     *
     * @throws AuthenticationException Or extend of this
     * @return iIdentity|void
     */
    abstract protected function doAuthenticate();

    /**
     * Get Instance of credential Object
     *
     * @param null|array|AbstractOptions $options Builder Options
     *
     * @return iCredential
     */
    abstract function newCredential($options = null);

    /**
     * Has Authenticated And Identifier Exists
     *
     * - it mean that Identifier has full filled identity
     *
     * note: this allow to register this authenticator as a service
     *       to retrieve authenticate information
     *
     * @return boolean
     */
    function hasAuthenticated()
    {
        return $this->identifier()->identity()->isFullFilled();
    }

    /**
     * Get Authenticated User Identifier
     *
     * note: this allow to register this authenticator as a service
     *       to retrieve authenticate information
     *
     * @return iIdentifier
     */
    function identifier()
    {
        if (!$this->identifier)
            $this->identifier = $this->getDefaultIdentifier();

        return $this->identifier;
    }


    // Options:

    /**
     * Set Default Identifier Instance
     *
     * @param iIdentifier $identifier
     *
     * @return $this
     */
    function setDefaultIdentifier(iIdentifier $identifier)
    {
        $this->default_identifier = $identifier;
        return $this;
    }

    /**
     * Get Default Identifier Instance
     *
     * @return iIdentifier|BaseIdentifier
     */
    function getDefaultIdentifier()
    {
        if (!$this->default_identifier)
            $this->setDefaultIdentifier(new BaseIdentifier);

        return $this->default_identifier;
    }


    // ...

    /**
     * Credential instance
     *
     * [code:]
     * // when options is passed it must init current credential and return
     * // self instead of credential
     *
     * $auth->credential([
     *   'username' => 'payam'
     *   , 'password' => '123456'
     *   , 'realm' => 'admin'
     *  ])->authenticate()
     * [code]
     *
     * - it`s contains credential fields used by
     *   authorize() to authorize user.
     *   maybe, user/pass or ip address in some case
     *   that we want auth. user by ip
     *
     * - it may be vary from within different Authorize
     *   services
     *
     * @param null|array $options
     * @return $this|iCredential
     */
    function credential($options = null)
    {
        if (!$this->credential)
            $this->credential = $this->newCredential();

        if ($options !== null) {
            $this->credential->from($options);
            return $this;
        }

        return $this->credential;
    }
}