<?php
declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SlopeIt\RepositoryDemo\Entity\Movie;
use SlopeIt\RepositoryDemo\Entity\Customer;
use SlopeIt\RepositoryDemo\Entity\Rental;
use SlopeIt\RepositoryDemo\Repository\RentalRepository;

class RentalRepositoryTest extends TestCase
{
    private static EntityManager $entityManager;

    private RentalRepository $SUT;

    public static function setUpBeforeClass(): void
    {
        self::$entityManager = require __DIR__ . '/../entity-manager-factory.php';
    }

    public function setUp(): void
    {
        $classMetadataFactory = new ClassMetadataFactory();
        $classMetadataFactory->setEntityManager(self::$entityManager);

        $this->SUT = new RentalRepository(
            self::$entityManager,
            $classMetadataFactory->getMetadataFor(Rental::class)
        );
    }

    public function tearDown(): void
    {
        // Clean up the database after each test
        self::$entityManager->clear();
        self::$entityManager->getConnection()->exec(
            "SET session_replication_role = replica;

            DO $$ DECLARE
                r RECORD;
            BEGIN
                FOR r IN (
                    SELECT tablename FROM pg_tables
                    WHERE schemaname = current_schema()
                ) LOOP
                    EXECUTE 'DELETE FROM ' || quote_ident(r.tablename);
                END LOOP;
            END $$;

            SET session_replication_role = DEFAULT;"
        );
    }

    #[Test]
    public function it_counts_rentals_made_by_a_specific_customer()
    {
        // Precondition
        $this->persistFixtures(
            $dune = new Movie('Dune'),
            $harryPotter = new Movie('Harry Potter e la pietra filosofale'),
            $mario = new Customer('Mario', 'Mario'),
            $luigi = new Customer('Luigi', 'Mario'),
            new Rental($dune, $mario, new \DateTimeImmutable()),
            new Rental($harryPotter, $mario, new \DateTimeImmutable()),
            new Rental($harryPotter, $luigi, new \DateTimeImmutable()),
        );

        // Action
        $resultCount = $this->SUT->countByCustomer($mario);

        // Verification: Mario rented both Dune and Harry Potter
        $this->assertSame(2, $resultCount);
    }

    #[Test]
    public function it_finds_rentals_made_after_a_certain_date_and_not_yet_returned()
    {
        $this->markTestIncomplete('This is not possible with just magic methods because it combines two properties.');
    }

    #[Test]
    public function it_finds_the_most_recent_rental_of_a_specific_movie()
    {
        // Preconditions: note that Peach's rental would be the most recent, but not for the movie we're looking for.
        $this->persistFixtures(
            $pulpFiction = new Movie('Pulp Fiction'),
            $bladeRunner = new Movie('Blade Runner'),
            $mario = new Customer('Mario', 'Mario'),
            $luigi = new Customer('Luigi', 'Mario'),
            $peach = new Customer('Peach', 'Toadstool'),
            new Rental($pulpFiction, $mario, new \DateTimeImmutable('1 week ago')),
            $luigiRental = new Rental($pulpFiction, $luigi, new \DateTimeImmutable('yesterday')),
            new Rental($bladeRunner, $peach, new \DateTimeImmutable('today')),
        );

        // Action
        $result = $this->SUT->findOneByMovie($pulpFiction, ['rentDate' => 'DESC']);

        // Verification
        $this->assertSame($luigiRental, $result);
    }

    private function persistFixtures(object ...$entities): void
    {
        foreach ($entities as $entity) {
            self::$entityManager->persist($entity);
        }
        self::$entityManager->flush();
    }
}
