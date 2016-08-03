Mobile Channel
==============

Purpose
-------

The Mobile channel is sending messages as mobile push notification 
via the [NotificationPusher library](https://github.com/Ph3nol/NotificationPusher).


Requirements
------------

If you want to use this channel, the [`NotificationPusher`](https://github.com/Ph3nol/NotificationPusher) library
must be installed.


Configuration
-------------

### Channel configuration

``` yaml
yokai_messenger:
    channels:
        mobile:
            environment: dev # or prod
            apns:
                certificate: /path/to/your/apns-certificate.pem
                pass_phrase: example
            gcm:
                api_key:     YourApiKey
```

If you do not understand these parameters, 
please see the [library documentation](https://github.com/Ph3nol/NotificationPusher/blob/master/doc/getting-started.md).


### Messages configuration

``` yaml
yokai_messenger:
    messages:
        -
            id:       <up to you>
            channels: mobile # or [mobile, <any other channels ...>]
```


Registering messages manually
-----------------------------

``` php
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yokai\MessengerBundle\DependencyInjection\Factory\MessageDefinitionFactory;

public function load(array $configs, ContainerBuilder $container)
{
    MessageDefinitionFactory::create($container, '<up to you>', ['mobile'], [], []);
}
```


Recipient
---------

This channel supports :

- objects that implements `Yokai\MessengerBundle\Recipient\MobileRecipientInterface` interface


Specificities
-------------

None.
