<?php 

namespace Peruse\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

class PeruseUser implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected $data;
    protected $token;

    /**
     * Set response
     *
     * @param array $response
     */
    public function __construct(array $response, AccessToken $token)
    {
        $this->data = $response;
        $this->token = $token;
    }

    /**
     * Returns the access token for user.
     *
     * @return AccessToken|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Returns the ID for the user as a string.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getField('id');
    }

    /**
     * Returns the full name for the user as a string.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getField('first_name') . ' ' . $this->getField('last_name');
    }

    /**
     * Returns the first name for the user as a string.
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getField('first_name');
    }

    /**
     * Returns the last name for the user as a string.
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->getField('last_name');
    }

    /**
     * Returns the email for the user as a string if available.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getField('email');
    }

    /**
     * Returns the connect date for the user as a string.
     *
     * @return string|null
     */
    public function getConnectDate()
    {
        return $this->getField('connect_date');
    }

    /**
     * Returns all the data obtained about the user.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data['data']['user'];
    }

     /**
     * Returns a field from the Graph node data.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    private function getField($key)
    {
        return isset($this->data['data']['user'][$key]) ? $this->data['data']['user'][$key] : null;
    }

}