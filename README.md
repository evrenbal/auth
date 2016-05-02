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