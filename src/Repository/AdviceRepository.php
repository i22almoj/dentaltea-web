<?php

namespace App\Repository;

use App\Entity\Advice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Advice>
 *
 * @method Advice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Advice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Advice[]    findAll()
 * @method Advice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advice::class);
    }

    public function add(Advice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Advice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function listAdvices($params=array(), $pagination=true): array
    {   
        $this->updateOrder();

        $qb = $this->createQueryBuilder('u');

        if(!empty($params['filter'])){
            $filter = $params['filter'];
            if(empty($filter['search'])&&isset($filter['search']))  unset($filter['search']);
            $search = (empty($filter['search']))? '' : $filter['search'];
            if(!empty($search)) $filter['search'] = '%'.$search.'%';
            $qb->setParameters($filter);
            if(!empty($search))   $qb->andWhere('u.title LIKE :search');
        }

        $qb->orderBy((!empty($params['orderby'])) ? 'u.'.$params['orderby'] : 'u.id', (!empty($params['order'])&&strtolower($params['order'])=='desc') ? 'DESC' : 'ASC');

        if($pagination && !empty($params) && is_array($params) && !empty($params['offset']) && !empty($params['p_size'])){
            $qb->setFirstResult($params['offset']);
            $qb->setMaxResults($params['p_size']);
        }else{
            $qb->setFirstResult(0);
        }
    
        $query = $qb->getQuery();
        
        return $query->execute();

    }

    public function updateOrder(){

        $qb = $this->createQueryBuilder('u')->orderBy('u.sortNumber', 'ASC');
        $advices = $qb->getQuery()->execute();

        if(empty($advices) || !is_array($advices))  return true;

        for($i=0;$i<sizeof($advices);$i++):
            $advices[$i]->setSortNumber(($i+1));
            $this->getEntityManager()->persist($advices[$i]);
			$this->getEntityManager()->flush();
        endfor;

    }

    public function adviceMoveUp($id){
        $this->updateOrder();

        $advice = $this->getEntityManager()->getRepository(Advice::class)->findOneBy(array('id' => intval($id)));
        
        if(empty($advice))  return false;

        $sortNumber = $advice->getSortNumber();
        if($sortNumber==1) return true;

        $qb = $this->createQueryBuilder('u')->orderBy('u.sortNumber', 'ASC');
        $advices = $qb->getQuery()->execute();

        if(empty($advices) || !is_array($advices))  return true;

        $advice->setSortNumber(($sortNumber-1));
        $advices[($sortNumber-2)]->setSortNumber($sortNumber);  
        $this->getEntityManager()->persist($advice);
        $this->getEntityManager()->persist($advices[($sortNumber-2)]);
        $this->getEntityManager()->flush();
    }

    public function adviceMoveDown($id){
        $this->updateOrder();

        $advice = $this->getEntityManager()->getRepository(Advice::class)->findOneBy(array('id' => intval($id)));
        
        if(empty($advice))  return false;

        $sortNumber = $advice->getSortNumber();
        
        $qb = $this->createQueryBuilder('u')->orderBy('u.sortNumber', 'ASC');
        $advices = $qb->getQuery()->execute();

        if(empty($advices) || !is_array($advices))  return true;

        if($sortNumber==sizeof($advices)) return true;


        $advice->setSortNumber(($sortNumber+1));
        $advices[($sortNumber)]->setSortNumber($sortNumber);  
        $this->getEntityManager()->persist($advice);
        $this->getEntityManager()->persist($advices[$sortNumber]);
        $this->getEntityManager()->flush();
    }
}
