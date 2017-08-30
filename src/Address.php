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

namespace Berlioz\Mailer;


use Berlioz\Mailer\Exception\InvalidArgumentException;

class Address
{
    /** @var string Name */
    private $name;
    /** @var string Mail */
    private $mail;

    public function __construct(string $mail = null, string $name = null)
    {
        if (!is_null($mail)) {
            $this->setMail($mail);
        }
        if (!is_null($name)) {
            $this->setName($name);
        }
    }

    /**
     * __toString() magic method.
     *
     * @return string
     */
    public function __toString()
    {
        // E-mail
        if (mb_strlen($this->name) > 0) {
            $str = sprintf('%s <%s>',
                           mb_encode_mimeheader($this->name, mb_detect_encoding($this->name), 'Q'),
                           $this->mail);
        } else {
            $str = $this->mail;
        }

        return $str;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get mail.
     *
     * @return string
     */
    public function getMail(): string
    {
        return $this->mail;
    }

    /**
     * Set mail.
     *
     * @param string $mail
     *
     * @throws \Berlioz\Mailer\Exception\InvalidArgumentException if email address isn\'t valid.
     */
    public function setMail(string $mail)
    {
        if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $this->mail = $mail;
        } else {
            throw new InvalidArgumentException(sprintf('"%s" isn\'t a valid email address', $mail));
        }
    }
}