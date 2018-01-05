<?php
namespace Peruse\OAuth2\Client\Test\Provider;

use Mockery as m;
use Peruse\OAuth2\Client\Provider\Connect;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;


class FooPeruseProvider extends Connect
{
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        return json_decode('{"data":{"user":{"id": 12345, "first_name": "mock_first_name", "last_name": "mock_last_name", "email": "mock_email", "connect_date": "mock_connect_date"}}}', true);
    }
}


class PeruseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Peruse
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new Connect([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none'
        ]);
    }
    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);
        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testGetBaseAccessTokenUrl()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);
        $this->assertEquals('/api/oauth2/exchange', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getHeader')
            ->times(1)
            ->andReturn('application/json');
        $response->shouldReceive('getBody')
            ->times(1)
            ->andReturn('{"access_token":"mock_access_token","token_type":"bearer","expires_in":3600, "refresh_token":"mock_refresh_token"}');
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertEquals('mock_refresh_token', $token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId(), 'Facebook does not return user ID with access token. Expected null.');
    }

    public function testScopes()
    {
        $this->assertEquals([], $this->provider->getDefaultScopes());
    }

    public function testUserData()
    {
        $provider = new FooPeruseProvider();
        $token = m::mock('League\OAuth2\Client\Token\AccessToken'); 
        $user = $provider->getResourceOwner($token);
        $this->assertEquals(12345, $user->getId($token));
        $this->assertEquals('mock_first_name mock_last_name', $user->getName($token));
        $this->assertEquals('mock_first_name', $user->getFirstName($token));
        $this->assertEquals('mock_last_name', $user->getLastName($token));
        $this->assertEquals('mock_email', $user->getEmail($token));
    }

}