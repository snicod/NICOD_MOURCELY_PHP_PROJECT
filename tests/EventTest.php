<?php

namespace App\Tests\Entity;

use App\Entity\Event;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testGetRemainingPlaces()
    {
        $event = new Event();
        $event->setNbParticipantMax(10);

        $this->assertSame(10, $event->getRemainingPlaces());

        $event->addParticipant(new \App\Entity\User());
        $this->assertSame(9, $event->getRemainingPlaces());
    }
}
