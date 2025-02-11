<?php

declare(strict_types=1);

namespace Setono\DoctrineORMBatcher\Batcher;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Setono\DoctrineORMBatcher\Batch\Batch;

final class NaiveIdBatcher extends IdBatcher
{
    /**
     * @var NumberBatcherInterface
     */
    private $numberBatcher;

    /**
     * @var int
     */
    private $count;

    public function __construct(ManagerRegistry $managerRegistry, string $class, NumberBatcherInterface $numberBatcher)
    {
        parent::__construct($managerRegistry, $class);

        $this->numberBatcher = $numberBatcher;
    }

    /**
     * @throws NoResultException
     * @throws MappingException
     *
     * @return iterable|Batch[]
     */
    public function getBatches(int $batchSize = 100): iterable
    {
        yield from $this->numberBatcher->getBatches($this->getMin(), $this->getMax(), $batchSize);
    }

    /**
     * @throws MappingException
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getSparseness(): int
    {
        if (null === $this->count) {
            $this->initCount();
        }

        $bestPossibleCount = ($this->getMax() - $this->getMin()) + 1;

        return (int) (100 - round($this->count / $bestPossibleCount * 100));
    }

    /**
     * @throws NonUniqueResultException
     */
    private function initCount(): void
    {
        $qb = $this->createQueryBuilder('COUNT(o) as c');

        $this->count = (int) $qb->getQuery()->getSingleScalarResult();
    }
}
