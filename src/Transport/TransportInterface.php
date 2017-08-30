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


interface TransportInterface
{
    /**
     * Get line feed.
     *
     * Need to be by default to "\r\n" value.
     *
     * @return string
     */
    public function getLineFeed(): string;

    /**
     * Sending of email.
     *
     * @param \Berlioz\Mailer\Mail $mail Mail
     */
    public function send(\Berlioz\Mailer\Mail $mail);

    /**
     * Mass sending of email.
     *
     * @param \Berlioz\Mailer\Mail      $mail      Mail
     * @param \Berlioz\Mailer\Address[] $addresses Address list
     * @param callable                  $callback  Callback called after each email sent
     */
    public function massSend(\Berlioz\Mailer\Mail $mail, array $addresses, callable $callback = null);
}