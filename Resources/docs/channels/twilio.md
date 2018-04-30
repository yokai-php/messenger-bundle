Doctrine Channel
================

Purpose
-------

The Twilio channel is sending messages as SMS via Twilio
using the [Twilio SDK library](https://github.com/twilio/twilio-php).


Requirements
------------

If you want to use this channel, the [`Twilio SDK library`](https://github.com/twilio/twilio-php) 
must be installed, enabled and configured.


Configuration
-------------

### Channel configuration

``` yaml
yokai_messenger:
    channels:
        twilio:
            enabled: true
            from: '+330601020304'
            api_id: 'azertyuiop'
            api_token: 'qsdfghjklm'
```

### Messages configuration

``` yaml
yokai_messenger:
    messages:
        -
            id:       <up to you>
            channels: twilio # or [twilio, <any other channels ...>]
```


Registering messages manually
-----------------------------

``` php
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yokai\MessengerBundle\DependencyInjection\Factory\MessageDefinitionFactory;

public function load(array $configs, ContainerBuilder $container)
{
    MessageDefinitionFactory::create($container, '<up to you>', ['twilio'], [], []);
}
```


Recipient
---------

This channel supports :

- objects that implements `Yokai\MessengerBundle\Recipient\TwilioeRecipientInterface` interface


Specificities
-------------

This bundles is only responsible of sending messages via SMS using Twilio solution.

By definition a SMS is a plain text. So the body rendered must not contains HTML code.

Plus the SMS doesn't have a subject, only a content. So subject is not required. 

For more informations about the Twilio configuration, see [Twilio's documentation](https://www.twilio.com/) 
