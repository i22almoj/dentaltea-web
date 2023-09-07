<?php

namespace App\Repository;

use App\Entity\Date;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Date>
 *
 * @method Date|null find($id, $lockMode = null, $lockVersion = null)
 * @method Date|null findOneBy(array $criteria, array $orderBy = null)
 * @method Date[]    findAll()
 * @method Date[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Date::class);
    }

    public function add(Date $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Date $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function listDates($params=array(), $pagination=true): array
    {   
        $qb = $this->createQueryBuilder('u');
        $qb->join('u.author', 'a');

        if(!empty($params['filter'])){
            $filter = $params['filter'];
            if(empty($filter['search'])&&isset($filter['search']))  unset($filter['search']);
            $search = (empty($filter['search']))? '' : $filter['search'];
            if(!empty($search)) $filter['search'] = '%'.$search.'%';
            if(!empty($filter['author'])) $qb->andWhere('u.author = :author ');
            if(!empty($search))   $qb->andWhere('u.description LIKE :search ');
            if(!empty($filter['author'])){   
                $qb->andWhere('a.id = :author '); 
            }
            $qb->setParameters($filter);
        }
        if($pagination && !empty($params) && is_array($params) && !empty($params['orderby']) 
        && !empty($params['order']) && !empty($params['offset']) && !empty($params['p_size'])){
            $qb->orderBy('u.'.$params['orderby'], $params['order']);
            $qb->setFirstResult($params['offset']);
            $qb->setMaxResults($params['p_size']);
        }else{
            $qb->orderBy('u.id', 'ASC');
            $qb->setFirstResult(0);
        }
    
        $query = $qb->getQuery();
        
        return $query->execute();

    }
}
