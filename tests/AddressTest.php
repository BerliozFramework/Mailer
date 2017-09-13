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

namespace Berlioz\Mailer\Tests;


use Berlioz\Mailer\Address;
use Berlioz\Mailer\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    public function testConstructorNotValidMail()
    {
        $this->expectException(InvalidArgumentException::class);
        new Address('namenotvalid.com');
    }

    public function testConstructorValidMail()
    {
        $address = new Address('name@notvalid.com');
        $this->assertInstanceOf(Address::class, $address);
        $address = new Address('ronan.giron@berlioz-framework.com');
        $this->assertInstanceOf(Address::class, $address);
        $address = new Address('alias+ronan@berlioz-framework.com');
        $this->assertInstanceOf(Address::class, $address);
    }

    public function testEmptyConstructor()
    {
        $address = new Address();
        $this->assertInstanceOf(Address::class, $address);
    }

    public function testGetters()
    {
        $address = new Address('ronan.giron@berlioz-framework.com', 'Ronan Giron');

        $this->assertEquals('ronan.giron@berlioz-framework.com', $address->getMail());
        $this->assertEquals('Ronan Giron', $address->getName());
    }

    public function testSetters()
    {
        $address = new Address;
        $address->setName('Ronan Giron');
        $address->setMail('ronan.giron@berlioz-framework.com');

        $this->assertEquals('ronan.giron@berlioz-framework.com', $address->getMail());
        $this->assertEquals('Ronan Giron', $address->getName());
    }
}
