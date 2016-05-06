# baka-Auth

MC Auth Library to avoid having to redo a user signup flow for apps. 

## Testing

```
codecept run
```

## Using

Add this to your service.php

```
/**
* UserData dependency injection for the system
*
* @return Session
*/
$di->set('userData', function() {

    $session = new \Baka\Auth\Models\Sessions();
    $request = new \Phalcon\Http\Request();

    return \Baka\Auth\Models\Sessions::start(1, $request->getClientAddress());
});
```

## Migratio

`$ phalcon migration --action=run --migrations=migrations --config=</path/to/config.php>`


## ENV

```
//AUTH
AUTH_COOKIE_NAME= 
AUTH_COOKIE_PATH=
AUTH_COOKIE_DOMAIN=
AUTH_COOKIE_SECURE=
AUTH_ALLOW_AUTOLOGIN=
AUTH_SESSION_LENGHT=
AUTH_MAX_AUTOLOGIN_TIME=
AUTH_MAX_AUTOLOGIN_ATTEMPS=
PAGE_INDEX = 0
SESSION_METHOD_COOKIE = 100
SESSION_METHOD_GET = 101
ANONYMOUS=0
``

## Router

```
$router->add('/users', array(
    'namespace' => 'Phalcon\\Controllers\\',
    'controller' => 'users',
    'action' => 'home',
));

$router->add('/users/sign-up', array(
    'namespace' => 'Phalcon\\Controllers\\',
    'controller' => 'users',
    'action' => 'signup',
));

$router->add('/users/thank-you', array(
    'namespace' => 'Phalcon\\Controllers\\',
    'controller' => 'users',
    'action' => 'thankyou',
));

```