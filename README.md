# Berlioz Mailer

[![Latest Version](https://img.shields.io/packagist/v/berlioz/mailer.svg?style=flat-square)](https://github.com/BerliozFramework/Mailer/releases)
[![Software license](https://img.shields.io/github/license/BerliozFramework/Mailer.svg?style=flat-square)](https://github.com/BerliozFramework/Mailer/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/com/BerliozFramework/Mailer/master.svg?style=flat-square)](https://travis-ci.com/BerliozFramework/Mailer)
[![Quality Grade](https://img.shields.io/codacy/grade/00aa697606b949ca8d759e2909b08eec/master.svg?style=flat-square)](https://www.codacy.com/manual/BerliozFramework/Mailer)
[![Total Downloads](https://img.shields.io/packagist/dt/berlioz/mailer.svg?style=flat-square)](https://packagist.org/packages/berlioz/mailer)

**Berlioz Mailer** is a PHP library for sending mail, with or without local server.

## Installation

### Composer

You can install **Berlioz Mailer** with [Composer](https://getcomposer.org/), it's the recommended installation.

```bash
$ composer require berlioz/mailer
```

### Dependencies

* **PHP** ^7.1 || ^8.0
* PHP extensions:
  * **FileInfo**
* Packages:
  * **psr/log**


## Usage

### Example

You can send simply the like this:

```php
use Berlioz\Mailer\Address;
use Berlioz\Mailer\Mail;
use Berlioz\Mailer\Mailer;

$mail = (new Mail())
            ->setSubject('Test of Berlioz/Mailer')
            ->setText('Text plain of my mail')
            ->setHtml('<p>Html text of my mail</p>')
            ->setFrom(new Address('sender@test.com', 'Me the sender'))
            ->setTo([new Address('recipient@test.com', 'The recipient')]); 

$mailer = new Mailer();
$mailer->send($mail);
```

### Mail

**\Berlioz\Mailer\Mail** it's the object representation of a mail.

#### Basic

```php
use Berlioz\Mailer\Address;
use Berlioz\Mailer\Mail;

$mail = new Mail();
$mail->setSubject('Subject of my mail')
     ->setText('Text plain of my mail')
     ->setHtml('<p>Html text of my mail</p>')
     ->setFrom(new Address('sender@test.com', 'Me the sender'))
     ->setTo([new Address('recipient@test.com', 'The recipient')]);
```

#### Attachments

To add downloadable attachment:

```php
use Berlioz\Mailer\Attachment;use Berlioz\Mailer\Mail;$attachment = new Attachment('/path/of/my/file.pdf');
$mail = new Mail();
$mail->addAttachment($attachment);
```

To attach an attachment to HTML content:
```php
use Berlioz\Mailer\Attachment;use Berlioz\Mailer\Mail;$attachment = new Attachment('/path/of/my/img.jpg');
$mail = new Mail();
$mail->addAttachment($attachment);

$html = '<p>Html content 1</p>';
$html .= '<img src="cid:' . $attachment->getId() . '">';
$html .= '<p>Html content 2</p>';

$mail->setHtml($html);
```

**WARNING:** call `$attachment->getId()` method, does that the attachment will be in inline disposition. Only uses this method for inline attachments.

### Transports

#### Defaults transports

Default transport is **\Berlioz\Mailer\Transport\PhpMail** uses internal **mail()** of PHP.

You can uses another available transport for direct communication with SMTP server: **\Berlioz\Mailer\Transport\Smtp**.

```php
use Berlioz\Mailer\Mailer;
use Berlioz\Mailer\Transport\Smtp;

$smtp = new Smtp(
    'smpt.test.com',
    'user@test.com',
    'password',
    25,
    ['timeout' => 5]
);
$mailer = new Mailer();
$mailer->setTransport($smtp);
```

```php
use Berlioz\Mailer\Mailer;
$mailer = new Mailer([
    'transport' => [
        'name' => 'smtp',
        'arguments' => [
            'host' => 'smpt.test.com',
            'username' => 'user@test.com',
            'password' => 'password',
            'port' => 25,
            'options' => ['timeout' => 5]
        ]
    ]
]);
```

#### Create a new transport

It's possible to create new transport for various reasons.
To do that, you need to create class who implements **\Berlioz\Mailer\Transport\TransportInterface** interface.
