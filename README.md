# Berlioz Mailer

**Berlioz Mailer** is a PHP library for sending mail, with or without local server.

## Installation

### Composer

You can install **Berlioz Mailer** with [Composer](https://getcomposer.org/), it's the recommended installation.

```bash
$ composer require berlioz/mailer
```

### Dependencies

* **PHP** >= 7.1
* PHP extensions:
  * **FileInfo**
* Packages:
  * **psr/log**


## Usage

### Example

You can send simply the like this:

```php
$mail = (new \Berlioz\Mailer\Mail())
            ->setSubject('Test of Berlioz/Mailer')
            ->setText('Text plain of my mail')
            ->setHtml('<p>Html text of my mail</p>')
            ->setFrom(new Address('sender@test.com', 'Me the sender'))
            ->setTo([new Address('recipient@test.com', 'The recipient')]); 

$mailer = new \Berlioz\Mailer\Mailer;
$mailer->send($mail);
```

### Mail

**\Berlioz\Mailer\Mail** it's the object representation of a mail.

#### Basic

```php
$mail = new \Berlioz\Mailer\Mail;
$mail->setSubject('Subject of my mail')
     ->setText('Text plain of my mail')
     ->setHtml('<p>Html text of my mail</p>')
     ->setFrom(new Address('sender@test.com', 'Me the sender'))
     ->setTo([new Address('recipient@test.com', 'The recipient')]);
```

#### Attachments

To add downloadable attachment:

```php
$attachment = new \Berlioz\Mailer\Attachment('/path/of/my/file.pdf');
$mail = new \Berlioz\Mailer\Mail;
$mail->addAttachment($attachment);
```

To attach an attachment to HTML content:
```php
$attachment = new \Berlioz\Mailer\Attachment('/path/of/my/img.jpg');
$mail = new \Berlioz\Mailer\Mail;
$mail->addAttachment($attachment);

$html = '<p>Html content 1</p>';
$html .= '<img src="cid:' . $attachment->getId() . '">';
$html .= '<p>Html content 2</p>';

$mail->setHtml($html);
```

**WARNING:** call `$attachment->getId()` method, does that the attachment will be in inline disposition. Only uses this method for inline attachments.

### Transports

#### Defaults transports

Default transport is **\Berlioz\Mailer\Transport\Mail** uses internal **mail()** of PHP.

You can uses another available transport for direct communication with SMTP server: **\Berlioz\Mailer\Transport\Smtp**.

```php
$smtp = new \Berlioz\Mailer\Transport\Smtp('smpt.test.com',
                                           'user@test.com',
                                           'password',
                                           25,
                                           ['timeout' => 5]);
$mailer = new \Berlioz\Mailer\Mailer;
$mailer->setTransport($smtp);
```

```php
$mailer = new \Berlioz\Mailer\Mailer(['transport' => ['name'      => 'smtp',
                                                      'arguments' => ['host'     => 'smpt.test.com',
                                                                      'username' => 'user@test.com',
                                                                      'password' => 'password',
                                                                      'port'     => 25,
                                                                      'options'  => ['timeout' => 5]]]]);
```

#### Create a new transport

It's possible to create new transport for various reasons.
To do that, you need to create class who implements **\Berlioz\Mailer\Transport\TransportInterface** interface.
