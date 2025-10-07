<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = new User();

        $user->setNombre('Juan');
        $user->setEmail('juan@gmail.com');
        $user->setPassword('juan1234');

        $this->assertSame('Juan', $user->getNombre());
        $this->assertSame('juan@gmail.com', $user->getEmail());
        $this->assertSame('juan1234', $user->getPassword());
        $this->assertSame('juan@gmail.com', $user->getUserIdentifier());
        $this->assertIsArray($user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }


}