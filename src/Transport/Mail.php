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

class Mail extends AbstractTransport implements TransportInterface
{
    /**
     * @inheritdoc
     * @throws \Berlioz\Mailer\Exception\TransportException
     */
    public function send(\Berlioz\Mailer\Mail $mail)
    {
        // To
        $toAddresses = $mail->getTo();
        if (count($toAddresses) > 0) {
            $to = implode(', ', $toAddresses);
        } else {
            $to = 'undisclosed-recipients:;';
        }

        // Headers
        $headers = $this->getHeaders($mail, ['To', 'Subject']);

        // Mail
        $result = @mb_send_mail($to,
                                $mail->getSubject(),
                                implode("\r\n", $this->getContents($mail)),
                                $headers);

        if (!$result) {
            throw new TransportException('Unable to send mail with mb_send_mail() PHP function.');
        }
    }
}