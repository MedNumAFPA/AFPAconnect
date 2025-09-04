<?php

namespace App\Tests\Controller;

use App\Entity\Workshop;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WorkshopsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $workshopRepository;
    private string $path = '/workshop/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->workshopRepository = $this->manager->getRepository(Workshop::class);

        foreach ($this->workshopRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Workshop index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'workshop[name]' => 'Testing',
            'workshop[description]' => 'Testing',
            'workshop[maxSlots]' => 'Testing',
            'workshop[roomNumber]' => 'Testing',
            'workshop[timeSlots]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->workshopRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Workshop();
        $fixture->setName('My Title');
        $fixture->setDescription('My Title');
        $fixture->setMaxSlots('My Title');
        $fixture->setRoomNumber('My Title');
        $fixture->setTimeSlots('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Workshop');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Workshop();
        $fixture->setName('Value');
        $fixture->setDescription('Value');
        $fixture->setMaxSlots('Value');
        $fixture->setRoomNumber('Value');
        $fixture->setTimeSlots('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'workshop[name]' => 'Something New',
            'workshop[description]' => 'Something New',
            'workshop[maxSlots]' => 'Something New',
            'workshop[roomNumber]' => 'Something New',
            'workshop[timeSlots]' => 'Something New',
        ]);

        self::assertResponseRedirects('/workshop/');

        $fixture = $this->workshopRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getMaxSlots());
        self::assertSame('Something New', $fixture[0]->getRoomNumber());
        self::assertSame('Something New', $fixture[0]->getTimeSlots());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Workshop();
        $fixture->setName('Value');
        $fixture->setDescription('Value');
        $fixture->setMaxSlots('Value');
        $fixture->setRoomNumber('Value');
        $fixture->setTimeSlots('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/workshop/');
        self::assertSame(0, $this->workshopRepository->count([]));
    }
}
