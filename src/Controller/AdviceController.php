<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Advice;
use App\Entity\Date;
use App\Entity\User;
use App\Entity\Sequence;
use App\Form\Type\DateFormType;


class AdviceController extends AbstractController
{
    private $repository;
	private $doctine;
	
	public function __construct(ManagerRegistry $doctrine){
		$this->doctrine = $doctrine;
		$this->repository = $doctrine->getRepository(Advice::class);
	}

/* 
	name: index
	description: Controls access to the advices
	path: /consejos
*/
    public function index(UserInterface $currentUser): Response
    {  
        $advices = $this->repository->listAdvices(
            array(
                'filter' => array(),
            ), false);
		
		return $this->render('user/advices/list.html.twig', [
			'list' => $advices
        ]);
    }
}
