<?php
namespace Poirot\AuthSystem\Authenticate\Authenticator;

use Poirot\AuthSystem\Authenticate\AbstractHttpAuthenticator;

class HttpSessionAuth extends AbstractHttpAuthenticator
{
    use TraitSessionAuth;

    /**
     * Login Authenticated User
     *
     * - Sign user in environment and server
     *   exp. store in session
     *
     * - logout current user if has
     *
     * @throws \Exception no identity defined
     * @return $this
     */
    function signIn()
    {
        if (!($identity = $this->identity) && !$identity->isFulfilled())
            throw new \Exception('Identity not exists or not fullfilled');

        $this->__session()->set(self::STORAGE_IDENTITY_KEY , $identity);
        return $this;
    }

    /**
     * Logout Authenticated User
     *
     * - it must destroy sign
     *   ie. destroy session or invalidate token in storage
     *
     * - clear identity
     *
     * @return void
     */
    function signOut()
    {
        $this->__session()->destroy();
        $this->identity()->clean();
    }

    /**
     * Has User Logged in?
     *
     * - login mean that user identity signed with signIn method
     *   exp. Exists in Session or as a header in Request Http or etc..
     *
     * - validate sign
     *   ie. with token it must be exists and validate on server
     *
     * @return boolean
     */
    function isSignIn()
    {
        if($this->__session()->has(self::STORAGE_IDENTITY_KEY))
            return true;

        return false;
    }
}