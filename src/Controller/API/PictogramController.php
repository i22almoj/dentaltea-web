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
use App\Entity\Pictogram;
use App\Repository\PictogramRepository;

class PictogramController extends BaseController
{

/* 
	name: index
	description: Gets the data of the registered pictograms
	path: /api/pictograms
*/ 
    public function index(Request $request): JsonResponse
    {
        if(empty($this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);
        
        $query = $this->doctrine->getRepository(Pictogram::class)->createQueryBuilder('p')->getQuery();
        $result = $query->getArrayResult();
        return new JsonResponse(['success' => true, 'msg' => 'Lista de pictogramas', 'data' => $result], Response::HTTP_CREATED, $this->headers);
    }
   
}