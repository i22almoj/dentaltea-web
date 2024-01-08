<?php

namespace App\Repository;

use App\Entity\Pictogram;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pictogram>
 *
 * @method Pictogram|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pictogram|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pictogram[]    findAll()
 * @method Pictogram[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PictogramRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pictogram::class);
    }
    
    public function findAllByAuthor($authorId)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.author = :authorId')
            ->setParameter('authorId', $authorId)
            ->getQuery()
            ->getResult();
    }
    
    public function add(Pictogram $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Pictogram $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function listPictograms($params=array(), $pagination=true): array
    {   
        $qb = $this->createQueryBuilder('u');

        if(!empty($params['filter'])){
            $filter = $params['filter'];
            
            $search = (empty($filter['search']))? '' : $filter['search'];
            
            if(!empty($search)){
                $filter['search'] = '%'.$search.'%';
                $qb->andWhere('u.description LIKE :search');
                $qb->setParameters($filter);
            }
        }

        $qb->orderBy((!empty($params['orderby'])) ? 'u.'.$params['orderby'] : 'u.id', (!empty($params['order'])&&strtolower($params['order'])=='desc') ? 'DESC' : 'ASC');

        if($pagination==true){
            $qb->setFirstResult((!empty($params['offset'])) ? intval($params['offset']) : 0 );
            $qb->setMaxResults((!empty($params['p_size'])) ? intval($params['p_size']) : 12 );
        }else{
            $qb->setFirstResult(0);
        }
        
        $query = $qb->getQuery();
      
        return $query->execute();

    }
}
