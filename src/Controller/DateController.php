<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Date;
use App\Entity\User;
use App\Entity\Sequence;
use App\Form\Type\UserDateFormType;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class DateController extends AbstractController
{
    private $repository;
	private $doctine;
	
	public function __construct(ManagerRegistry $doctrine){
		$this->doctrine = $doctrine;
		$this->repository = $doctrine->getRepository(Date::class);
	}

/* 
	name: index
	description: Controls access to the dates calendar
	path: /
*/
	public function index(UserInterface $currentUser, ManagerRegistry $doctrine): Response
    {  
		$dates = $this->repository->listDates(
            array(
                'filter' => array(
                    'author' => $currentUser->getId()
                )
            ), false);
		
		return $this->render('user/dates/list.html.twig', [
			'dates' => $dates
        ]);
    }


/* 
	name: redirectHome(
	description: Redirect to home (/)
	path: /citas
*/	
	public function redirectHome(){
		return $this->redirectToRoute('home');
	}

/* 
	name: edit
	description: Controls access to the date edit form
	path: /citas/editar/{id}
*/
	public function edit($id, Request $request, UserInterface $currentUser, JWTEncoderInterface $jwtEncoder): Response
	{
		$date = $this->repository->findOneby(array('id' => intval($id)));
		
		if(empty($date) || $date->getAuthor()->getId()!=$currentUser->getId()) 
			return $this->redirectToRoute('dates');
        
		$form = $this->createForm(UserDateFormType::class, $date);
		
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid()){
			
			$date->setCreationTime(new \Datetime('now'));
			
			$sequence_id = intval($form->get('sequence_id')->getData());
			$sequence = $this->doctrine->getRepository(Sequence::class)->find($sequence_id);
			if(!empty($sequence) && $sequence instanceof Sequence && 
			($sequence->getPublic()==1 || $sequence->getAuthor()->getId()==$date->getAuthor()->getId())
			){
				$date->setSequence($sequence);
			}else{
				$date->setSequence(null);
			}

            $em = $this->getDoctrine()->getManager();			
			$em->persist($date);
			$em->flush();

            $this->addFlash(
                'notice',
                'Cambios guardados correctamente.'
            );

			return $this->redirectToRoute('dates');
		}

		$token = $jwtEncoder->encode([
			'email' => $currentUser->getEmail(),
			'exp' => time() + 3600]
		);

		$baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

		$back = $baseurl;

		return $this->render('admin/dates/edit.html.twig', [
			'form' => $form->createView(),
			'date' => $date,
			'token' => $token,
			'back' => $back
        ]);
	}

/* 
	name: new
	description: Controls access to date creation form
	path: /citas/nueva
*/	
	public function new(Request $request, UserInterface $currentUser, JWTEncoderInterface $jwtEncoder): Response
	{	

		$date = new Date($currentUser);
		$date->setCreationTime(new \Datetime('now'));
		$date->setAuthor($currentUser);
		$form = $this->createForm(UserDateFormType::class, $date);
		
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid() ) {
			
			$date->setCreationTime(new \Datetime('now'));
			
			$sequence_id = intval($form->get('sequence_id')->getData());
			$sequence = $this->doctrine->getRepository(Sequence::class)->find($sequence_id);
			if(!empty($sequence) && $sequence instanceof Sequence && 
			($sequence->getPublic() || $sequence->getAuthor()->getId()==$date->getAuthor()->getId())
			){
				$date->setSequence($sequence);
			}else{
				$date->setSequence(null);
			}

			$em = $this->getDoctrine()->getManager();
			$em->persist($date);
			$em->flush();

			$this->addFlash(
                'notice',
                'Cita creada correctamente.'
            );

			return $this->redirectToRoute('dates');
		}

		$token = $jwtEncoder->encode([
			'email' => $currentUser->getEmail(),
			'exp' => time() + 3600]
		);

		$baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

		$back = $baseurl;

        return $this->render('admin/dates/new.html.twig', [
			'form' => $form->createView(),
			'back' => $back,
			'token' => $token
        ]);
	}

/* 
	name: delete
	description: Controls access to delete a date
	path: /citas/eliminar/{id}
*/		
	public function delete($id, UserInterface $currentUser, Request $request): Response
	{

		$date = $this->repository->findOneby(array('id' => intval($id)));
        
		if(empty($date) || $date->getAuthor()->getId()!=$currentUser->getId()) 
			return $this->redirectToRoute('dates');

		if(empty($date)) return $this->redirectToRoute('admin_dates');
		
		$this->doctrine->getManager()->remove($date);
		$this->doctrine->getManager()->flush();

		$this->addFlash(
			'notice',
			'Cita eliminada correctamente.'
		);

		return $this->redirect($request->headers->get('referer'));
	}
	
}
