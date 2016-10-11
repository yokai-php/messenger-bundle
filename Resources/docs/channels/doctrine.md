Doctrine Channel
================

Purpose
-------

The Doctrine channel is registering messages as a database record 
using the [Doctrine ORM library](https://github.com/doctrine/doctrine2).


Requirements
------------

If you want to use this channel, the [`DoctrineBundle`](https://github.com/doctrine/DoctrineBundle) 
must be installed, enabled and configured.

See [Symfony's documentation](http://symfony.com/doc/current/doctrine.html).


Configuration
-------------

### Channel configuration

``` yaml
yokai_messenger:
    channels:
        doctrine:
            enabled: true
            manager: default
```

### Messages configuration

``` yaml
yokai_messenger:
    messages:
        -
            id:       <up to you>
            channels: doctrine # or [doctrine, <any other channels ...>]
```


Registering messages manually
-----------------------------

``` php
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yokai\MessengerBundle\DependencyInjection\Factory\MessageDefinitionFactory;

public function load(array $configs, ContainerBuilder $container)
{
    MessageDefinitionFactory::create($container, '<up to you>', ['doctrine'], [], []);
}
```


Recipient
---------

This channel supports :

- objects that implements `Yokai\MessengerBundle\Recipient\DoctrineRecipientInterface` interface


Specificities
-------------

This bundle is only responsible registering messages in a database 
(fetch messages and display it via the UI is up to you).

The entity you must ask for is `Yokai\MessengerBundle\Entity\Notification`.

Attachments are handled and registered on database. The entity you must ask for is 
`Yokai\MessengerBundle\Entity\NotificationAttachment`.

Attachment names are registered on the database, files are put on a folder you have to defined for each messages with
 attachment. 
 
``` yaml
yokai_messenger:
 messages:
     -
         id:       <up to you>
         channels: doctrine # or [doctrine, <any other channels ...>]
        defaults:
            ...
        options:
            doctrine:
                attachments_path: '/path/to/your/folder'
```
