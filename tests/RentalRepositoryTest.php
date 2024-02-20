<?php
declare(strict_types=1);

use Doctrine\Common\Collections\Criteria;
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
        $expr = Criteria::expr();
        $resultCount = $this->SUT->matching(
            Criteria::create()->where($expr->eq('customer', $mario))
        )->count();

        // Verification: Mario rented both Dune and Harry Potter
        $this->assertSame(2, $resultCount);
    }

    #[Test]
    public function it_finds_rentals_made_after_a_certain_date_and_not_yet_returned()
    {
        // Preconditions
        $this->persistFixtures(
            $forrestGump = new Movie('Forrest Gump'),
            $starWars = new Movie('Star Wars'),
            $mario = new Customer('Mario', 'Mario'),
            $luigi = new Customer('Luigi', 'Mario'),
            $marioRental1 = new Rental($forrestGump, $mario, new \DateTimeImmutable('7 days ago')),
            $marioRental2 = new Rental($starWars, $mario, new \DateTimeImmutable('7 days ago')),
            $luigiRental = new Rental($starWars, $luigi, new \DateTimeImmutable('7 days ago'))
        );

        $luigiRental->return(new \DateTimeImmutable());
        $marioRental2->return(new \DateTimeImmutable());
        $this->persistFixtures($luigiRental, $marioRental2);

        // Action
        $expr = Criteria::expr();
        $results = $this->SUT->matching(
            Criteria::create()->where($expr->gt('rentDate', new \DateTimeImmutable('8 days ago')))
               ->andWhere($expr->isNull('returnDate'))
        );

        // Verifications: even though Mario rented 2 movies, one was returned.
        $this->assertCount(1, $results);
        $this->assertContains($marioRental1, $results);
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
        $expr = Criteria::expr();
        $result = $this->SUT->matching(
            Criteria::create()
                ->where($expr->eq('movie', $pulpFiction))
                ->orderBy(['rentDate' => 'DESC'])
        )->offsetGet(0);

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
