<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUser()
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password');
        $user->setPrenom('John');
        $user->setNom('Doe');

        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('password', $user->getPassword());
        $this->assertSame('John', $user->getPrenom());
        $this->assertSame('Doe', $user->getNom());
    }
}
