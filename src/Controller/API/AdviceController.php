<?php

namespace App\Controller\API;

use App\Controller\API\BaseController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\Advice;
use App\Repository\AdviceRepository;

class AdviceController extends BaseController
{

/* 
	name: index
	description: Gets the data of the registered advices
	path: /api/advices
*/        
    public function index(Request $request): JsonResponse
    {
        if(empty($this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);
        
        $query = $this->doctrine->getRepository(Advice::class)->createQueryBuilder('a')->orderBy('a.sortNumber', 'ASC')->getQuery();
        $result = $query->getArrayResult();
        return new JsonResponse(['success' => true, 'msg' => 'Lista de consejos', 'data' => $result], Response::HTTP_CREATED, $this->headers);
    }
   
}