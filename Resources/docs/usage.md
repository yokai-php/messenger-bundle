Usage
=====

Example
-------

For this documentation we will take this message configuration as base example

``` yaml
yokai_messenger:
    messages:
        -
            id:       activity_report
            channels: [swiftmailer, doctrine]
            defaults: 
                subject:             Daily activity report
                template:            messages/activity_report/{group}/default.html.twig
                template_parameters: [ '{group}' ]
                template_vars:       [ 'recipient' ]
            options: 
                swiftmailer: 
                    subject:            {name} - your daily activity report
                    subject_parameters: [ '{name}' ]
                    template:           messages/activity_report/{group}/swiftmailer.html.twig
                doctrine: 
                    subject:            Please see your daily activity report
                    template_vars:      [ 'recipient', 'message' ]
```

This will declare a message named `activity_report`, 
that will be distributed over the `swiftmailer` & `doctrine` channels.

Considering that `{group}` value is "default" & `{name}` value is "John" :

- When distributed via the `swiftmailer` channel, 
    the message subject will be **John - your daily activity report**
    and message body will be the result of the **messages/activity_report/default/swiftmailer.html.twig** interpretation.

- When distributed via the `doctrine` channel, 
    the message subject will be **Please see your daily activity report**
    and message body will be the result of the **messages/activity_report/default.html.twig** interpretation.


The Content Builder
-------------------

You might notice in the example above that we talked about subject and template, 
but we never explained anything about it...

You are free to defined as many options as you want in your channels, 
but the bundle is expecting to do things with some of these options.
This is mainly about giving you a way to configure the message subject and body.

This done is job by `Yokai\MessengerBundle\Helper\ContentBuilder`.

When the sender is creating a delivery, it will find appropriate options for the channel and ask to the content builder
to build message subject and body.

### Building subject

The subject is built using [Symfony's translation component](http://symfony.com/doc/current/components/translation.html), 
options available are :

- `subject`: a translation message id
- `subject_parameters`: an array of parameter names to use in the translation
- `translation_catalog`: a translation domain

### Building body

The body is built using [Symfony's templating component](http://symfony.com/doc/current/components/templating.html), 
options available are :

- `template`: a template name (or pattern)
- `template_parameters`: an array of parameter names to determine the template name
- `template_vars`: an array of parameter names to provide as template vars


When you will ask to deliver the message 

``` php
$sender->send(
    'activity_report',
    $recipient,
    [
        '{name}' => $recipient->getName(),
        '{group}' => $recipient->getNotificationGroup(),
        'recipient' => $recipient,
        'message' => 'You have been busy today!',
    ],
    [
        # attachments
        Symfony\Component\HttpFoundation\File\File,
        Symfony\Component\HttpFoundation\File\File,
        ...
    ]
);
```

According that :

- `$sender` is the message sender service
- `$recipient` is a valid `Yokai\MessengerBundle\Recipient\SwiftmailerRecipientInterface`
- `$recipient` is a valid `Yokai\MessengerBundle\Recipient\DoctrineRecipientInterface`
- `$recipient->getName()` is returning "John Doe"
- `$recipient->getNotificationGroup()` is returning "member"


The **subject** will be :
- for the `swiftmailer` channel : 
    the string `John Doe - your daily activity report`
- for the `doctrine` channel : 
    the string `Please see your daily activity report`

The **body** will be :
- for the `swiftmailer` channel : 
    the `messages/activity_report/member/swiftmailer.html.twig` template 
    rendered with `['recipient' => $recipient]` parameters
- for the `doctrine` channel : 
    the `messages/activity_report/member/default.html.twig` template 
    rendered with `['recipient' => $recipient, 'message' => 'You have been busy today!']` parameters

