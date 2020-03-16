<?php

namespace PeterColes\Betfair\Api;

use Exception;
use PeterColes\Betfair\Http\Client as BetfairHttpClient;

class Auth extends BaseApi
{
    /**
     * Betfair API endpoint for authentication requests
     */
    private const ENDPOINT = 'https://identitysso.betfair.com/api/';

    /**
     * 4 hours, expressed in seconds
     */
    const SESSION_LENGTH = 4 * 60 * 60;
    /**
     * API fail status
     */
    const API_STATUS_FAIL = 'FAIL';
    /**
     * @var string
     */
    protected $appKey;
    /**
     * @var string
     */
    protected $username;
    /**
     * @var string
     */
    protected $password;
    /**
     * Session token, provided by Betfair at login
     */
    public $sessionToken = null;
    /**
     * Time of last login, expressed in seconds since the Unix Epoch
     */
    public $lastLogin = null;

    public function __construct( string $appKey, string $username, string $password )
    {
        $this->appKey = $appKey;
        $this->username = $username;
        $this->password = $password;
        parent::__construct( Auth::ENDPOINT );
    }

    /**
     * Wrapper method for other methods to initiate and manage a Betfair session.
     * This method can be called safely multiple times in a script, or in a loop
     * for a long running process and will only trigger the authentication overhead
     * when really needed.
     *
     */
    public function init()
    {
        if ($this->sessionRemaining() > 5) {
            $this->keepAlive();
        } else {
            $this->login($this->appKey, $this->username, $this->password);
        }
    }

    /**
     * Accept app key and session token and extend session.
     *
     * @param  string $appKey
     * @param  string|null $sessionToken
     * @return string
     */
    public function persist($appKey, $sessionToken)
    {
        if ($sessionToken === null) {
            throw new Exception('Invalid session token');
        }

        $this->appKey = $appKey;
        $this->sessionToken = $sessionToken;

        return $this->keepAlive();
    }

    /**
     * Method to directly execute Betfair login request.
     * For use only when the init() method isn't appropriate.
     *
     * @param  string $appKey
     * @param  string $username
     * @param  string $password
     * @return string
     * @throws Exception
     */
    public function login($appKey, $username, $password)
    {
        $this->appKey = $appKey;

        $request = $this->httpClient
            ->setMethod('post')
            ->setEndPoint(self::ENDPOINT.'login/')
            ->setFormData([ 'username' => $username, 'password' => $password ]);

        $result = $this->execute($request);

        $this->sessionToken = $result->token;
        $this->lastLogin = time();

        return $result->token;
    }

    /**
     * Execute Betfair API call to extend the current session.
     * Implicitly uses the already set app key and session token.
     *
     * @return string
     * @throws Exception
     */
    public function keepAlive()
    {
        $result = $this->execute($this->httpClient->setEndPoint(self::ENDPOINT.'keepAlive/'));

        $this->lastLogin = time();

        return $result->token;
    }

    /**
     * Execute Betfair API call to logout from their system.
     * Clear all local references to the session.
     *
     * @throws Exception
     */
    public function logout()
    {
        $this->execute($this->httpClient->setEndPoint($this->endpoint.'logout/'));
        $this->sessionToken = null;
        $this->lastLogin = null;
    }

    /**
     * Calculate and provide the time remaining until the current session token expires.
     *
     * @return integer
     */
    public function sessionRemaining()
    {
        if ($this->sessionToken === null) {
            return 0;
        }

        return $this->lastLogin + self::SESSION_LENGTH - time();
    }

    /**
     * Accept request, add auth headers and dispatch, then respond to any errors.
     *
     * @param  BetfairHttpClient $client
     * @return Mixed
     * @throws Exception
     */
    public function execute($client)
    {
        $result = $client->authHeaders( $this->getHeaders() )->send();

        if ($result->status === self::API_STATUS_FAIL) {
            throw new Exception('Error: '.$result->error);
        }

        return $result;
    }

    public function getHeaders(): array {
        return [ 'X-Application' => $this->appKey, 'X-Authentication' => $this->sessionToken ];
    }
}
