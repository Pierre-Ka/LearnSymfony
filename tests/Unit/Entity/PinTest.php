<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Pin;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PinTest extends KernelTestCase
{
    private ?ObjectManager $entityManager;

    public function testSomething(): void
    {
        $p1 = new Pin();
        $p1->setTitle('tottoto');
        $p1->setDescription('description');

        $this->entityManager->persist($p1);
        $this->entityManager->flush();

        $this->assertNotNull($p1->getCreatedAt());
        $this->assertNull($p1->getUpdatedAt());

        $this->assertEquals('tottoto', $p1->getTitle());

        $p1->setTitle('titi');
        $this->entityManager->persist($p1);
        $this->entityManager->flush();

        $this->assertNotNull($p1->getUpdatedAt());
    }

    public function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }
}
