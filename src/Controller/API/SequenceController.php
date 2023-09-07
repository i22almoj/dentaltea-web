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
use App\Entity\Sequence;
use App\Entity\Pictogram;
use App\Entity\SequencePictogram;

class SequenceController extends BaseController
{

/* 
	name: index
	description: Gets the data of the user's sequences
	path: /api/sequences
*/     
    public function index(Request $request): JsonResponse
    {
        if(empty($user = $this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);
        $data = [];
        $query = $this->doctrine->getManager()->createQuery('
            SELECT s, sp, p
            FROM App\Entity\Sequence s
            LEFT JOIN s.sequencePictograms sp
            LEFT JOIN sp.pictogram p
            WHERE s.author = :authorId
        ');
        $query->setParameter('authorId', $user->getId()); 
        $data['userSequences'] = $query->getArrayResult();

        $query = $this->doctrine->getManager()->createQuery('
            SELECT s, sp, p
            FROM App\Entity\Sequence s
            LEFT JOIN s.sequencePictograms sp
            LEFT JOIN sp.pictogram p
            WHERE s.author <> :authorId AND s.public = 1
        ');
        $query->setParameter('authorId', $user->getId()); 
        $data['publicSequences'] = $query->getArrayResult();

        return new JsonResponse(['success' => true, 'msg' => 'Lista de apoyos visuales', 'data' => $data], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: item
	description: Gets the data of a sequence
	path: /api/sequences/item
*/      
    public function item(Request $request): JsonResponse
    {
        if(empty($user = $this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);

        $params = $_GET; 

        if(!empty($params['sequence_id'])){
            $object = $this->doctrine->getRepository(Sequence::class)->findOneById(intval($params['sequence_id']));
        }

        if(empty($object))
        {
            return new JsonResponse(['error' => 'Debes introducir un ID de apoyo visual correcto', 'params' => $params], Response::HTTP_BAD_REQUEST, $this->headers);
        }
        if($user->getRole()!='ROLE_ADMIN'&&$object->getAuthor()->getId()!=$user->getId()&&$object->getPublic()!=1){
            return new JsonResponse(['error' => 'No tienes permisos para visualizar este elemento'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        $query = $this->doctrine->getManager()->createQuery('
            SELECT s, sp, p
            FROM App\Entity\Sequence s
            LEFT JOIN s.sequencePictograms sp
            LEFT JOIN sp.pictogram p
            WHERE s.id = :sequenceId
        ');
        $query->setParameter('sequenceId', $object->getId()); 
        $result = $query->getArrayResult();
        $sequence = !empty($result) ? $result[0] : array();

        if($user->getRole()=='ROLE_ADMIN'||$object->getAuthor()->getId()==$user->getId()){
            $sequence['edit'] = true;
        }else{
            $sequence['edit'] = false;
        }

        return new JsonResponse(['success' => true, 'msg' => 'Datos de apoyo visual', 'data' => $sequence], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: add
	description: Adds a sequence
	path: /api/sequences/add
*/      
    public function add(Request $request): JsonResponse
    {
        if(empty($user = $this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);

        $params = json_decode($request->getContent(), true); 

        if(empty($params['description'])){
            return new JsonResponse(['error' => 'Debes introducir una descripción'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        $sequencePictograms = $params['sequencePictograms'];
        if(empty($sequencePictograms)||!is_array($sequencePictograms)){
            return new JsonResponse(['error' => 'Debes añadir al menos un pictograma'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        $sequence = new Sequence($user);

        $sequence->setCreationTime(new \Datetime('now'));
        
        $sequence->setDescription($params['description']);
        
        $this->doctrine->getManager()->persist($sequence);
        $this->doctrine->getManager()->flush();

        $this->addPictograms($sequence, $sequencePictograms);

        return new JsonResponse(['success' => true, 'msg' => 'Apoyo visual creado correctamente', 'data' => []], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: edit
	description: Updates a sequence
	path: /api/sequences/edit
*/     
    public function edit(Request $request): JsonResponse
    {
        if(empty($user = $this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);

        $params = json_decode($request->getContent(), true); 

        if(!empty($params['sequence_id'])){
            $sequence = $this->doctrine->getRepository(Sequence::class)->findOneById(intval($params['sequence_id']));
        }

        if(empty($sequence))
        {
            return new JsonResponse(['error' => 'Debes introducir un ID de apoyo visual correcto', 'params' => $params], Response::HTTP_BAD_REQUEST, $this->headers);
        }
        if($user->getRole()!='ROLE_ADMIN'&&$sequence->getAuthor()->getId()!=$sequence->getId()&&$sequence->getPublic()!=1){
            return new JsonResponse(['error' => 'No tienes permisos para editar este elemento'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        if(empty($params['description'])){
            return new JsonResponse(['error' => 'Debes introducir una descripción'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        $sequencePictograms = $params['sequencePictograms'];
        if(empty($sequencePictograms)||!is_array($sequencePictograms)){
            return new JsonResponse(['error' => 'Debes añadir al menos un pictograma'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        $sequence->setDescription($params['description']);
        
        $this->doctrine->getManager()->persist($sequence);
        $this->doctrine->getManager()->flush();

        $this->addPictograms($sequence, $sequencePictograms);

        return new JsonResponse(['success' => true, 'msg' => 'Apoyo visual actualizado correctamente', 'data' => []], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: delete
	description: Deletes a sequence
	path: /api/sequences/delete
*/     
    public function delete(Request $request): JsonResponse
    {
        if(empty($user = $this->getTokenUser($request)))    
            return new JsonResponse(['success' => false, 'msg' => 'Usuario no autorizado'], Response::HTTP_UNAUTHORIZED, $this->headers);

        $params = $request->query->all();

        if(!empty($params['sequence_id'])){
            $sequence = $this->doctrine->getRepository(Sequence::class)->findOneById(intval($params['sequence_id']));
        }
        if(empty($sequence))
        {
            return new JsonResponse(['success' => false, 'msg' => 'Debes introducir un ID de apoyo visual correcto'], Response::HTTP_BAD_REQUEST, $this->headers);
        }

        if($sequence->getAuthor()->getId()!=$user->getId()&&$user->getRole()!='ROLE_ADMIN'){
            return new JsonResponse(['success' => false, 'msg' => 'No tienes permisos para eliminar este apoyo visual'], Response::HTTP_BAD_REQUEST, $this->headers);
        }
		
        $this->doctrine->getManager()->remove($sequence);	
		$this->doctrine->getManager()->flush();

        return new JsonResponse(['success' => true, 'msg' => 'Apoyo visual eliminado correctamente', 'data' => $params], Response::HTTP_CREATED, $this->headers);
    }

/* 
	name: clearPictograms
	description: Removes all pictograms from a sequence
*/	    
    private function clearPictograms($sequence){
		if(empty($sequence)||empty($sequence->getId())) return false;

		$items = $sequence->getSequencePictograms();
		
		if(!empty($items)){
			foreach($items as $item){
				$this->doctrine->getManager()->remove($item);
			}
		}
		$this->doctrine->getManager()->flush();
		
		return true;
	}

/* 
	name: addPictograms
	description: Adds pictograms to a sequence
*/	    
    private function addPictograms($sequence, $sequencePictograms){
		
		if(empty($sequence)||empty($sequence->getId())) return false;

        $this->clearPictograms($sequence);

		if(empty($sequencePictograms)||!is_array($sequencePictograms)) return true;
		
        $i = 0;
		foreach($sequencePictograms as $item){ 
            $i++;
			$pictogram = $this->doctrine->getRepository(Pictogram::class)->findOneBy(array('id' =>intval($item['pictogram_id'])));
			if(empty($pictogram)) continue;
			
			$sortItem = new SequencePictogram();
			$sortItem->setSequence($sequence);
			$sortItem->setPictogram($pictogram);
			$sortItem->setDescription($item['description']);
			$sortItem->setSortNumber(intval($i));
			
			$this->doctrine->getManager()->persist($sortItem);
			$this->doctrine->getManager()->flush();
		}
	}

}