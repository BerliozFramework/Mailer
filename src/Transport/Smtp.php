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

namespace Berlioz\Mailer\Transport;


use Berlioz\Mailer\Exception\TransportException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;

class Smtp extends AbstractTransport implements TransportInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /** @var string Host */
    private $host;
    /** @var int Port */
    private $port;
    /** @var int Timeout */
    private $timeout;
    /** @var string Username */
    private $username;
    /** @var string Password */
    private $password;
    /** @var resource Resource */
    private $resource;
    /** @var string Last request */
    private $lastRequest;
    /** @var string Last response */
    private $lastResponse;

    public function __construct(string $host = null,
                                string $username = null,
                                string $password = null,
                                int $port = 25,
                                array $options = [])
    {
        // Defaults
        $this->host = $host ?? 'localhost';
        $this->port = $port ?? 25;
        $this->username = $username;
        $this->password = $password;
        $this->timeout = $options['timeout'] ?? 10;
    }

    /**
     * Smtp destructor.
     */
    public function __destruct()
    {
        if ($this->isConnected()) {
            $this->disconnect();
        }
    }

    /**
     * __set_state() magic method.
     *
     * @param array $an_array Properties array.
     *
     * @return array
     */
    public static function __set_state($an_array): array
    {
        return ['host'     => $an_array['host'],
                'port'     => $an_array['port'],
                'timeout'  => $an_array['timeout'],
                'username' => $an_array['username'],
                'password' => '*** HIDDEN ***'];
    }

    /**
     * __debugInfo() magic method.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return ['host'     => $this->host,
                'port'     => $this->port,
                'timeout'  => $this->timeout,
                'username' => $this->username,
                'password' => '*** HIDDEN ***'];
    }

    /**
     * Connect to resource.
     *
     * @return void
     * @throws \Berlioz\Mailer\Exception\TransportException
     */
    private function connect(): void
    {
        try {
            $errno = null;
            $errstr = null;

            $this->resource = fsockopen($this->host,
                                        $this->port,
                                        $errno,
                                        $errstr,
                                        $this->timeout);

            if (false === $this->resource) {
                throw new TransportException('Connection timeout');
            } else {
                if ($this->get($response) != '220') {
                    // Log
                    $this->log(LogLevel::ERROR, sprintf('Connection error: %s', $response));

                    throw new TransportException('Connection refused');
                } else {
                    // Log
                    $this->log(LogLevel::DEBUG, sprintf('Connection response: %s', $response));

                    $this->write($request = "HELO " . trim(gethostname()));

                    if ($this->get($response) != "250") {
                        // Log
                        $this->log(LogLevel::ERROR, sprintf('HELO command error: %s', $response));

                        throw new TransportException('"HELO" command failed');
                    } else {
                        // Log
                        $this->log(LogLevel::DEBUG, sprintf('HELO command response: %s', $response));

                        if (!empty($this->username) && !empty($this->password)) {
                            $this->write('AUTH PLAIN ' . base64_encode("\000" . $this->username . "\000" . $this->password));

                            if ($this->get($response) != '235') {
                                // Log
                                $this->log(LogLevel::ERROR, sprintf('Login failed: %s', $response));

                                throw new TransportException('Login failed');
                            } else {
                                // Log
                                $this->log(LogLevel::DEBUG, sprintf('Login success: %s', $response));
                            }
                        } else {
                            // Log
                            $this->log(LogLevel::DEBUG, 'No login');
                        }
                    }
                }
            }
        } catch (TransportException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new TransportException('Connection error', 0, $e);
        }
    }

    /**
     * Disconnect resource.
     *
     * @return void
     * @throws \Berlioz\Mailer\Exception\TransportException
     */
    private function disconnect(): void
    {
        try {
            if (is_resource($this->resource)) {
                $this->write('QUIT');

                if ($this->get($response) != '221') {
                    // Log
                    $this->log(LogLevel::ERROR, sprintf('QUIT command error: %s', $response));

                    throw new TransportException('Disconnection failed');
                } else {
                    // Log
                    $this->log(LogLevel::DEBUG, sprintf('QUIT command response: %s', $response));
                }

                @fclose($this->resource);
                $this->resource = false;
            }
        } catch (\Exception $e) {
            throw new TransportException('Connection error', 0, $e);
        }
    }

    /**
     * Is connected ?
     *
     * @return bool
     */
    private function isConnected(): bool
    {
        return is_resource($this->resource);
    }

    /**
     * Get the resource data.
     *
     * @param mixed $response Complete response
     *
     * @return bool|string Return code of SMTP command
     * @throws \Berlioz\Mailer\Exception\TransportException
     */
    private function get(&$response = null)
    {
        if ($this->isConnected()) {
            if (($response = fgets($this->resource)) === false) {
                throw new TransportException('Reading failed');
            } else {
                $response = trim($response);
                $this->lastResponse = $response;

                return substr($response, 0, 3);
            }
        } else {
            throw new TransportException('Not connected');
        }
    }

    /**
     * Write data to resource.
     *
     * @param $data
     *
     * @return void
     * @throws \Berlioz\Mailer\Exception\TransportException
     */
    private function write($data): void
    {
        if ($this->isConnected()) {
            if (is_array($data)) {
                $data = implode($this->getLineFeed(), $data);
            }

            $this->lastRequest = $data;

            // Windows
            if (stristr(PHP_OS, 'WIN')) {
                $data = str_replace("\n.", "\n..", $data);
            }

            if (!fwrite($this->resource, $data . $this->getLineFeed())) {
                throw new TransportException('Write failed');
            }
        } else {
            throw new TransportException('Not connected');
        }
    }

    /**
     * @inheritdoc
     */
    public function send(\Berlioz\Mailer\Mail $mail): void
    {
        $response = '';

        if (!$this->isConnected()) {
            $this->connect();
        }

        try {
            $this->write(sprintf('MAIL FROM: <%s>', $mail->getFrom()->getMail()));

            if ($this->get($response) != '250') {
                // Log
                $this->log(LogLevel::ERROR, sprintf('MAIL FROM command error: %s', $response));

                throw new TransportException('"MAIL FROM" command failed');
            } else {
                // Log
                $this->log(LogLevel::DEBUG, sprintf('MAIL FROM command response: %s', $response));

                // Addresses
                {
                    $addresses = array_merge($mail->getTo(), $mail->getCc(), $mail->getCci());
                    if (count($addresses) > 0) {
                        /** @var \Berlioz\Mailer\Address $address */
                        foreach ($addresses as $address) {
                            $this->write(sprintf('RCPT TO: <%s>', $address->getMail()));

                            if ($this->get($response) != '250') {
                                // Log
                                $this->log(LogLevel::ERROR, sprintf('RCPT TO command error: %s', $response));

                                throw new TransportException(sprintf('"RCPT TO" command failed for "" email address', $address->getMail()));
                            } else {
                                // Log
                                $this->log(LogLevel::DEBUG, sprintf('RCPT TO command response: %s', $response));
                            }
                        }
                    } else {
                        throw new TransportException('No recipients for the mail');
                    }
                }

                // Data
                $this->write('DATA');

                if ($this->get($response) != '354') {
                    // Log
                    $this->log(LogLevel::ERROR, sprintf('DATA command error: %s', $response));

                    throw new TransportException('DATA command failed');
                } else {
                    // Log
                    $this->log(LogLevel::DEBUG, sprintf('DATA command response: %s', $response));

                    // Headers
                    $this->write($this->getHeaders($mail));

                    // Body
                    $this->write($this->getContents($mail));

                    // End of mail
                    $this->write('.');

                    if ($this->get($response) != '250') {
                        // Log
                        $this->log(LogLevel::ERROR, sprintf('End of data command error: %s', $response));

                        throw new TransportException('Write of data failed');
                    } else {
                        // Log
                        $this->log(LogLevel::DEBUG, sprintf('End of data command response: %s', $response));
                    }
                }
            }
        } catch (TransportException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new TransportException('Connection error', 0, $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function massSend(\Berlioz\Mailer\Mail $mail, array $addresses, callable $callback = null): void
    {
        if (!$this->isConnected()) {
            $this->connect();
        }

        parent::massSend($mail, $addresses, $callback);
    }

    /**
     * Logs.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    private function log($level, $message, array $context = []): void
    {
        // Log
        if (!is_null($this->logger)) {
            $this->logger->log($level,
                               sprintf('{class} / {host} / %s', $message),
                               array_merge($context,
                                           ['class' => __CLASS__,
                                            'host'  => $this->host]));
        }
    }
}