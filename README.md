# Peruse Provider for OAuth 2.0 Client


This package provides Peruse OAuth 2.0 support for PHP.

This package is compliant with [PSR-1][], [PSR-2][], [PSR-4][], and [PSR-7][]. If you notice compliance oversights,
please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-7]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md


## Requirements

The following versions of PHP are supported.

* PHP 5.6
* PHP 7.0
* PHP 7.1
* HHVM

## Installation

Add the following to your `composer.json` file.

```json
{
    "require": {
        "tilersmyth/oauth2-peruse": "^1.0"
    }
}
```

## Usage

### Authorization Code Flow

```php
session_start();

$provider = new \League\OAuth2\Client\Provider\Connect([
    'clientId'          => '{facebook-app-id}',
    'clientSecret'      => '{facebook-app-secret}',
    'redirectUri'       => 'https://example.com/callback-url'
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl([
        'scope' => ['email'],
    ]);
    $_SESSION['oauth2state'] = $provider->getState();
    
    echo '<a href="'.$authUrl.'">Connect with Peruse!</a>';
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    echo 'Invalid state.';
    exit;

}

// Try to get an access token (using the authorization code grant)
$token = $provider->getAccessToken('authorization_code', [
    'code' => $_GET['code']
]);

// Optional: Now you have a token you can look up a users profile data
try {

    // We got an access token, let's now get the user's details
    $user = $provider->getResourceOwner($token);

    // Use these details to get user data
    printf('Hello %s!', $user->getFirstName());
    
    echo '<pre>';
        var_dump($user);
    echo '</pre>';

} catch (\Peruse\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

    // Failed to get the user details.
    exit($e->getMessage());
}

echo '<pre>';
// Use this to interact with an API on the users behalf
var_dump($token->getToken());
# string(217) "CAADAppfn3msBAI7tZBLWg...

// The time (in epoch time) when an access token will expire
var_dump($token->getExpires());
# int(1436825866)
echo '</pre>';
```

### The PeruseUser Entity

When using the `getResourceOwner()` method to obtain the user node, it will be returned as a `PeruseUser` entity.

```php
$user = $provider->getResourceOwner($token);

$id = $user->getId();
var_dump($id);
# string(3) "103"

$name = $user->getName();
var_dump($name);
# string(10) "John Smith"

$firstName = $user->getFirstName();
var_dump($firstName);
# string(4) "John"

$lastName = $user->getLastName();
var_dump($lastName);
# string(5) "Smith"

# Requires the "email" permission
$email = $user->getEmail();
var_dump($email);
# string(13) "john@test.com"

# Date user connects to application (ISO Date Format)
$connectedDate = $user->getConnectDate();
var_dump($connectedDate);
# string(28) "2017-12-18T02:01:26.027+0000"

```

You can also get all the data from the User node with `toArray()`.

```php
$userData = $user->toArray();
```


### Refreshing a Token

Once your application is authorized, you can refresh an expired token using a refresh token rather than going through the entire process of obtaining a brand new token. To do so, simply reuse this refresh token from your data store to request a refresh.

```php
$provider = new \League\OAuth2\Client\Provider\Connect([
    'clientId'          => '{facebook-app-id}',
    'clientSecret'      => '{facebook-app-secret}',
    'redirectUri'       => 'https://example.com/callback-url'
]);

$existingAccessToken = getAccessTokenFromYourDataStore();

if ($existingAccessToken->hasExpired()) {
    $newAccessToken = $provider->getAccessToken('refresh_token', [
        'refresh_token' => $existingAccessToken->getRefreshToken()
    ]);

    // Purge old access token and store new access token to your data store.
}

```

## Testing

``` bash
$ ./vendor/bin/phpunit
```


## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/oauth2-facebook/blob/master/LICENSE) for more information.