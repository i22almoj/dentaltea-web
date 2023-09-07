<?php

namespace App\Repository;

use App\Entity\Sequence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Repository\UserRepository;

/**
 * @extends ServiceEntityRepository<Sequence>
 *
 * @method Sequence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sequence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sequence[]    findAll()
 * @method Sequence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SequenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sequence::class);
    }

    public function add(Sequence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sequence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function listSequences($params=array(), $pagination=true): array
    {   
        $qb = $this->createQueryBuilder('u');
        $qb->join('u.author', 'a');

        if(!empty($params['filter'])){
            $filter = $params['filter'];
            $search = (empty($filter['search']))? '' : $filter['search'];
            if(!empty($search))   $qb->andWhere('u.description LIKE :search');
            if(!empty($filter['author'])){   
                $qb->andWhere('a.id = :author '); 
            }
            $qb->setParameters($filter);
            
        }
        if($pagination==true){
            $qb->orderBy((!empty($params['orderby'])) ? 'u.'.$params['orderby'] : 'u.id', (!empty($params['order'])&&strtolower($params['order'])=='desc') ? 'DESC' : 'ASC');
            $qb->setFirstResult((!empty($params['offset'])) ? intval($params['offset']) : 0 );
            $qb->setMaxResults((!empty($params['p_size'])) ? intval($params['p_size']) : 12 );
        }else{
            $qb->orderBy('u.id', 'ASC');
            $qb->setFirstResult(0);
        }
        
        $query = $qb->getQuery();
      
        return $query->execute();

    }

    public function listPublicSequences($currentUser=null): array
    {   
        $qb = $this->createQueryBuilder('u');

        $qb->where('u.public = :public')->setParameter('public', '1');

        if(!empty($currentUser)){
            $qb->andWhere('u.author != :author_id')->setParameter('author_id', $currentUser->getId());
        }

        $qb->orderBy('u.id', 'ASC');
        $qb->setFirstResult(0);
    
        
        $query = $qb->getQuery();
      
        return $query->execute();

    }

    protected function build(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(BarRepository::class)
            ->addTag('doctrine.repository_service')
        ;
    }
}