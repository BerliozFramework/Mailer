<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Mailer;

use Berlioz\Mailer\Exception\InvalidArgumentException;
use Berlioz\Mailer\Transport\PhpMail as MailTransport;
use Berlioz\Mailer\Transport\TransportInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;

class Mailer implements LoggerAwareInterface
{
    /** Default transports into the package. */
    const DEFAULT_TRANSPORTS = [
        'smtp' => '\Berlioz\Mailer\Transport\Smtp',
        'mail' => '\Berlioz\Mailer\Transport\PhpMail'
    ];
    /** @var \Berlioz\Mailer\Transport\TransportInterface Transport */
    private $transport;
    /** @var LoggerInterface The logger instance. */
    private $logger;

    /**
     * Mailer constructor.
     *
     * @param array $options
     *
     * @throws \Berlioz\Mailer\Exception\InvalidArgumentException
     * @throws \ReflectionException
     */
    public function __construct(array $options = [])
    {
        // Transport
        if (!empty($options['transport'])) {
            $classArgs = [];

            if (is_array($options['transport'])) {
                if (!empty($options['transport']['name'])) {
                    if (empty(self::DEFAULT_TRANSPORTS[$options['transport']['name']])) {
                        throw new InvalidArgumentException(
                            sprintf('Unknown "%s" default transport', $options['transport']['name'])
                        );
                    }

                    $className = self::DEFAULT_TRANSPORTS[$options['transport']['name']];
                } else {
                    if (empty($options['transport']['class'])) {
                        throw new InvalidArgumentException('Missing class name in "transport" options');
                    }

                    $className = $options['transport']['class'];
                }

                if (!empty($options['transport']['arguments'])) {
                    if (!is_array($options['transport']['arguments'])) {
                        throw new InvalidArgumentException('Class arguments of "transport" options must be an array');
                    }

                    $classArgs = $options['transport']['arguments'];
                }
            } else {
                if (!is_string($options['transport'])) {
                    throw new InvalidArgumentException('"transport" options must be an array or a valid class name');
                }

                $className = $options['transport'];
            }

            if (!class_exists($className)) {
                throw new InvalidArgumentException(sprintf('Class "%s" doesn\'t exists', $className));
            }

            $class = new ReflectionClass($className);
            $object = $class->newInstanceArgs($classArgs);

            if (!$object instanceof TransportInterface) {
                throw new InvalidArgumentException(
                    'Transport class must be an instance of \Berlioz\Mailer\Transport\TransportInterface interface'
                );
            }

            $this->setTransport($object);
        }
    }

    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        if (null !== $this->transport && $this->transport instanceof LoggerAwareInterface) {
            /** @var LoggerAwareInterface $transport */
            $transport = $this->transport;
            $transport->setLogger($this->logger);
        }
    }

    /**
     * Get transport.
     *
     * @return \Berlioz\Mailer\Transport\TransportInterface
     */
    public function getTransport(): TransportInterface
    {
        if (null === $this->transport) {
            $this->transport = new MailTransport();
        }

        return $this->transport;
    }

    /**
     * Set transport.
     *
     * @param \Berlioz\Mailer\Transport\TransportInterface $transport
     *
     * @return static
     */
    public function setTransport(TransportInterface $transport): Mailer
    {
        // Logger
        if ($transport instanceof LoggerAwareInterface && null !== $this->logger) {
            $transport->setLogger($this->logger);
        }

        $this->transport = $transport;

        return $this;
    }

    /**
     * Sending of email.
     *
     * @param \Berlioz\Mailer\Mail $mail Mail
     *
     * @return mixed Depends of transport
     * @throws \Berlioz\Mailer\Exception\TransportException if an error occurred during sending of mail.
     */
    public function send(Mail $mail)
    {
        return $this->getTransport()->send($mail);
    }

    /**
     * Mass sending of email.
     *
     * @param \Berlioz\Mailer\Mail $mail Mail
     * @param \Berlioz\Mailer\Address[] $addresses Address list
     * @param callable $callback Callback called after each email sent
     *
     * @return mixed Depends of transport
     * @throws \Berlioz\Mailer\Exception\TransportException if an error occurred during sending of mail.
     */
    public function massSend(Mail $mail, array $addresses, callable $callback = null)
    {
        return $this->getTransport()->massSend($mail, $addresses, $callback);
    }
}