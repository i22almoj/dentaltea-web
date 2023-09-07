<?php

namespace App\Repository;

use App\Entity\SequencePictogram;
use App\Entity\Sequence;
use App\Entity\Pictogram;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SequencePictogram>
 *
 * @method SequencePictogram|null find($id, $lockMode = null, $lockVersion = null)
 * @method SequencePictogram|null findOneBy(array $criteria, array $orderBy = null)
 * @method SequencePictogram[]    findAll()
 * @method SequencePictogram[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SequencePictogramRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SequencePictogram::class);
    }

    public function add(SequencePictogram $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SequencePictogram $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}