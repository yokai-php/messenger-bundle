Configuration
=============


Channels
--------

Please see per channel documentation :

- `swiftmailer` : [read](channels/swiftmailer.md)
- `doctrine` : [read](channels/doctrine.md)
- `mobile` : [read](channels/mobile.md)
- `twilio` : [read](channels/twilio.md)


Messages
--------

### Structure

Even if creating a message is as simple registering a service of certain class and certain tag,
you can also register messages with this bundle configuration.

A message :

- **must** have a unique string identifier (you will use it whenever you will want to send it)
- **must** be registered over at least 1 channel
- **can** have default options
- **can** have per channel options

``` yaml
yokai_messenger:
    messages:
        -
            id:       <the message identifier>
            channels: <an array of channels>
            defaults: <a hash of default options>
            options: 
                <a channel id>: <a hash of options for this channel>
```
