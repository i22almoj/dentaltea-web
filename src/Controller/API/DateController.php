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
use App\Entity\Sequence;
use App\Repository\SequenceRepository;
use App\Entity\Date;
use App\Repository\DateRepository;

class DateController extends BaseController
{

/* 
	name: index
	description: Gets the data of the user's dates
	path: /api/dates
*/ 
    public function index(Request $request): JsonResponse
    {
        if(empty($user = $this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);
        
        $query = $this->doctrine->getRepository(Date::class)->createQueryBuilder('a')->getQuery();
        $query = $this->doctrine->getManager()->createQuery('
            SELECT d
            FROM App\Entity\Date d
            WHERE d.author = :authorId
        ');
        $query->setParameter('authorId', $user->getId()); 
        $result = $query->getArrayResult();

        $result = $query->getArrayResult();
        return new JsonResponse(['success' => true, 'msg' => 'Lista de citas', 'data' => $result], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: item
	description: Gets the data of a date
	path: /api/dates/item
*/     
    public function item(Request $request): JsonResponse
    {
        if(empty($user = $this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);

        $params = $_GET; 

        if(!empty($params['date_id'])){
            $object = $this->doctrine->getRepository(Date::class)->findOneById(intval($params['date_id']));
        }

        if(empty($object))
        {
            return new JsonResponse(['error' => 'Debes introducir un ID de cita correcto'], Response::HTTP_BAD_REQUEST, $this->headers);
        }
        if($user->getRole()!='ROLE_ADMIN'&&$object->getAuthor()->getId()!=$user->getId()){
            return new JsonResponse(['error' => 'No tienes permisos para visualizar este elemento'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        $query = $this->doctrine->getManager()->createQuery('
            SELECT d, s, sp, p
            FROM App\Entity\Date d
            LEFT JOIN d.sequence s
            LEFT JOIN s.sequencePictograms sp WITH s.id = sp.sequence
            LEFT JOIN sp.pictogram p  WITH p.id = sp.pictogram
            WHERE d.id = :dateId
            ORDER BY sp.sortNumber ASC
        ');
        $query->setParameter('dateId', $object->getId()); 
        $result = $query->getArrayResult();
        $date = !empty($result) ? $result[0] : array();

        return new JsonResponse(['success' => true, 'msg' => 'Datos de cita', 'data' => $date], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: add
	description: Adds a date
	path: /api/dates/add
*/    
    public function add(Request $request): JsonResponse
    {
        if(empty($user = $this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);

        $params = json_decode($request->getContent(), true); 

        $date = new \App\Entity\Date($user);

        $date->setCreationTime(new \Datetime('now'));
        
        if(empty($params['description'])){
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir una descripción'], Response::HTTP_BAD_REQUEST, $this->headers);
        }else{
            $date->setDescription($params['description']);
        }

        if(empty($params['dateTime'])){
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir fecha y hora'], Response::HTTP_BAD_REQUEST, $this->headers);
        }
        
        $dateTime = date('Y-m-d H:i:s', strtotime($params['dateTime']));
        if(empty($dateTime)){
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir fecha y hora correctas'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        $dateTime = new \DateTime('@'.strtotime($dateTime));
        $date->setDateTime($dateTime);

        $sequence = null;
        if(!empty($params['sequence_id'])){
            $sequence = $this->doctrine->getRepository(Sequence::class)->findOneById(intval($params['sequence_id']));

            if(empty($sequence))   $sequence = null;
        }
        $date->setSequence($sequence);

        if(!empty($params['notificationsMobile'])&&$params['notificationsMobile']==1){
            $date->setNotificationsMobile(1);
        }else{
            $date->setNotificationsMobile(0);
        }

        if(!empty($params['notificationsEmail'])&&$params['notificationsEmail']==1){
            $date->setNotificationsEmail(1);
        }else{
            $date->setNotificationsEmail(0);
        }

        $this->doctrine->getManager()->persist($date);
        $this->doctrine->getManager()->flush();

        $params['date_id'] = $date->getId();

        return new JsonResponse(['success' => true, 'msg' => 'Cita añadida correctamente', 'data' => $params], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: edit
	description: Updates a date
	path: /api/dates/edit
*/     
    public function edit(Request $request): JsonResponse
    {
        if(empty($user = $this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);

        $params = json_decode($request->getContent(), true); 

        if(!empty($params['date_id'])){
            $date = $this->doctrine->getRepository(Date::class)->findOneById(intval($params['date_id']));
        }
        if(empty($date))
        {
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir un ID de cita correcto'], Response::HTTP_BAD_REQUEST, $this->headers);
        }
        
        if($date->getAuthor()->getId()!=$user->getId()&&$user->getRole()!='ROLE_ADMIN'){
            return new JsonResponse(['success' => false, 'msg' => 'No tienes permisos para editar esta cita'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        if(empty($params['description'])){
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir una descripción'], Response::HTTP_BAD_REQUEST, $this->headers);
        }else{
            $date->setDescription($params['description']);
        }

        if(empty($params['dateTime'])){
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir fecha y hora'], Response::HTTP_BAD_REQUEST, $this->headers);
        }
        
        $dateTime = date('Y-m-d H:i:s', strtotime($params['dateTime']));
        if(empty($dateTime)){
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir fecha y hora correctas'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        $dateTime = new \DateTime('@'.strtotime($dateTime));
        $date->setDateTime($dateTime);

        $sequence = null;
        if(!empty($params['sequence_id'])){
            $sequence = $this->doctrine->getRepository(Sequence::class)->findOneById(intval($params['sequence_id']));

            if(empty($sequence))   $sequence = null;
        }
        $date->setSequence($sequence);

        if(!empty($params['notificationsMobile'])&&$params['notificationsMobile']==1){
            $date->setNotificationsMobile(1);
        }else{
            $date->setNotificationsMobile(0);
        }

        if(!empty($params['notificationsEmail'])&&$params['notificationsEmail']==1){
            $date->setNotificationsEmail(1);
        }else{
            $date->setNotificationsEmail(0);
        }

        $this->doctrine->getManager()->persist($date);
        $this->doctrine->getManager()->flush();

        return new JsonResponse(['success' => true, 'msg' => 'Cita actualizada correctamente', 'data' => $params], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: delete
	description: Deletes a date
	path: /api/dates/delete
*/     
    public function delete(Request $request): JsonResponse
    {
        if(empty($user = $this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);

        $params = $request->query->all();

        if(!empty($params['date_id'])){
            $date = $this->doctrine->getRepository(Date::class)->findOneById(intval($params['date_id']));
        }
        if(empty($date))
        {
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir un ID de cita correcto'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        if($date->getAuthor()->getId()!=$user->getId()&&$user->getRole()!='ROLE_ADMIN'){
            return new JsonResponse(['success' => false, 'msg' => 'No tienes permisos para eliminar esta cita'], Response::HTTP_BAD_REQUEST, $this->headers);
        }
		
        //Eliminamos la nota
        $this->doctrine->getManager()->remove($date);	
		$this->doctrine->getManager()->flush();

        return new JsonResponse(['success' => true, 'msg' => 'Cita eliminada correctamente', 'data' => $params], Response::HTTP_CREATED, $this->headers);
    }
   

}