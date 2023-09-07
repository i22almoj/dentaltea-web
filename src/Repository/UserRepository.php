<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function findAllOrderedByName() { 
        $qb = $this->createQueryBuilder('u')->addOrderBy('u.name', 'ASC');
        $query = $qb->getQuery();

        return $query->execute();
    } 

    public function listUsers($params=array(), $pagination=true): array
    {   
        $qb = $this->createQueryBuilder('u');

        if(!empty($params['filter'])){
            $filter = $params['filter'];
            if(empty($filter['search'])&&isset($filter['search']))  unset($filter['search']);
            $search = (empty($filter['search']))? '' : $filter['search'];
            if(!empty($search)) $filter['search'] = '%'.$search.'%';
            $qb->setParameters($filter);
            if(!empty($filter['role'])) $qb->andWhere('u.role = :role ');
            if(!empty($filter['status']))	$qb->andWhere('u.status = :status ');
            if(!empty($search))   $qb->andWhere('u.name LIKE :search OR u.email LIKE :search');
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
