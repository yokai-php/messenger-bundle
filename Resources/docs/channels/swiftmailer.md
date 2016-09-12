Swift Mailer Channel
====================

Purpose
-------

The Swift Mailer channel is sending messages as email 
via the [Swift Mailer library](https://github.com/swiftmailer/swiftmailer).


Requirements
------------

If you want to use this channel, the [`SwiftmailerBundle`](https://github.com/symfony/swiftmailer-bundle) 
must be installed, enabled and configured.

See [Symfony's documentation](http://symfony.com/doc/current/email.html).


Configuration
-------------

### Channel configuration

``` yaml
yokai_messenger:
    channels:
        swiftmailer:
            enabled:            true
            from:               no-reply@acme.org
            translator_catalog: notifications
```

### Messages configuration

``` yaml
yokai_messenger:
    messages:
        -
            id:       <up to you>
            channels: swiftmailer # or [swiftmailer, <any other channels ...>]
```

### Messages overriding channel configuration

``` yaml
yokai_messenger:
    messages:
        -
            id:       <up to you>
            channels: swiftmailer # or [swiftmailer, <any other channels ...>]
            options:
                swiftmailer:
                    from:       override@acme.org
```


Registering messages manually
-----------------------------

``` php
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yokai\MessengerBundle\DependencyInjection\Factory\MessageDefinitionFactory;

public function load(array $configs, ContainerBuilder $container)
{
    MessageDefinitionFactory::create($container, '<up to you>', ['swiftmailer'], [], []);
}
```


Recipient
---------

This channel supports :

- objects that implements `Yokai\MessengerBundle\Recipient\SwiftmailerRecipientInterface` interface
- string that looks like an email (see [`filter_var` with `FILTER_VALIDATE_EMAIL`](http://php.net/manual/function.filter-var.php))


Specificities
-------------

The channel is only responsible for creating `Swift_Message` instance, 
and sending it through the application mailer (`Swift_Mailer`).

The `Swift_Message` configuration is done by a `chain of configurator`, 
you can plug-in into this process by creating your own `configurator`.

Creating a configurator is as simple as creating a service that implements 
`Yokai\MessengerBundle\Channel\Swiftmailer\Configurator\SwiftMessageConfiguratorInterface`
with the `yokai_yokai_messenger.swiftmailer_message_configurator` tag.

This bundle come with some built-in `configurator`:

- `Yokai\MessengerBundle\Channel\Swiftmailer\Configurator\DefaultMessageConfigurator`
