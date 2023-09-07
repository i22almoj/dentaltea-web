<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Date;
use App\Entity\User;
use App\Entity\Sequence;
use App\Form\Type\DateFormType;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class DateController extends BaseController
{
    private $repository;
	private $doctine;
	
	public function __construct(ManagerRegistry $doctrine){
		$this->doctrine = $doctrine;
		$this->repository['date'] = $doctrine->getRepository(Date::class);
		$this->repository['user'] = $doctrine->getRepository(User::class);
	}

/* 
	name: index
	description: Controls access to the dates list in the admin section
	path: /admin/citas
*/	
    public function index(UserInterface $currentUser): Response
    {  
        if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');
		
		$dates = $this->getList(array('p_size' => 24, 'filter_fields' => ['author']));
		$authors = $this->repository['user']->findAllOrderedByName();
		return $this->render('admin/dates/list.html.twig', [
			'dates' => $dates,
			'list' => $this->list,
			'authors' => $authors
        ]);
    }

/* 
	name: listQuery
	description: Performs the query for the paginated list
*/		
	public function listQuery($pagination=true): Array{
		return $this->repository['date']->listDates($this->list, $pagination);
	}

/* 
	name: edit
	description: Controls access to the date edit form  in the admin section
	path: /admin/citas/editar/{id}
*/	
	public function edit($id, Request $request, UserInterface $currentUser, JWTEncoderInterface $jwtEncoder): Response
	{
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');

		$date = $this->repository['date']->findOneby(array('id' => intval($id)));
		
		if(empty($date)) 
			return $this->redirectToRoute('admin_dates');
	
		$form = $this->createForm(DateFormType::class, $date);
		
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid()){
			
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
                'Cambios guardados correctamente.'
            );

			return $this->redirectToRoute('admin_dates');
		}
		
		$token = $jwtEncoder->encode([
			'email' => $currentUser->getEmail(),
			'exp' => time() + 3600]
		);
		
		$baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

		$back = $baseurl.'/admin/citas';

		return $this->render('admin/dates/edit.html.twig', [
			'form' => $form->createView(),
			'date' => $date,
			'token' => $token,
			'back' => $back
        ]);
	}

/* 
	name: new
	description: Controls access to date creation form in the admin section
	path: /admin/citas/nueva
*/		
	public function new(Request $request, UserInterface $currentUser, JWTEncoderInterface $jwtEncoder): Response
	{	
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('admin_dates');

		$date = new Date($currentUser);
		$date->setCreationTime(new \Datetime('now'));
		$form = $this->createForm(DateFormType::class, $date);
		
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

			return $this->redirectToRoute('admin_dates');
		}

		$token = $jwtEncoder->encode([
			'email' => $currentUser->getEmail(),
			'exp' => time() + 3600
		]);

		$baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

		$back = $baseurl.'/admin/citas';

        return $this->render('admin/dates/new.html.twig', [
			'form' => $form->createView(),
			'token' => $token,
			'back' => $back
        ]);
	}

/* 
	name: delete
	description: Controls access to delete a date in the admin section
	path: /admin/citas/eliminar/{id}
*/		
	public function delete($id, UserInterface $currentUser, Request $request): Response
	{
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');

		$date = $this->repository['date']->findOneby(array('id' => intval($id)));

		if(empty($date)) return $this->redirectToRoute('admin_dates');
		
		$this->doctrine->getManager()->remove($date);
		$this->doctrine->getManager()->flush();

		$this->addFlash(
			'notice',
			'Cita eliminada correctamente.'
		);

		return $this->redirect($request->headers->get('referer'));
	}

/* 
	name: getTokenUser
	description: Gets the data of a user associated with a given token of an ajax call
*/  	
	public function getTokenUser(Request $request, JWTEncoderInterface $jwtEncoder)
    {
    	try {
			$token = str_replace('Bearer ', '', $request->headers->get('Authorization'));
            $payload =  $jwtEncoder->decode($token);
        } catch (\Exception $e) {
            return false;
        }

		if(empty($payload)||empty($payload['email']))	return false;
        $user = $this->doctrine
            ->getRepository(User::class)
            ->findOneBy(['email' => $payload['email']]);

        if (empty($user)|| !($user instanceof User) ) {
            return false;
        }

        return $user;
    }


/* 
	name: ajaxUserSequences
	description: Returns a list of the user's sequences in json
	path: /admin/ajax/user-sequences
*/	
	public function ajaxUserSequences(Request $request, JWTEncoderInterface $jwtEncoder): Response{ 
		$currentUser = $this->getTokenUser($request, $jwtEncoder);
		
		if(empty($currentUser)){
			return  $this->json([
				'result' => 'error',
				'message' => 'El token de sesión no es válido'
			]);
		}

		$user_id = $request->query->get('user_id');
		$user = $this->doctrine->getRepository(User::class)->find(intval($user_id));

		$repository = $this->doctrine->getRepository(Sequence::class);

		$data = ['user' => $user ];
		$query = $this->doctrine->getManager()->createQuery('
            SELECT s, sp, p
            FROM App\Entity\Sequence s
            LEFT JOIN s.sequencePictograms sp
            LEFT JOIN sp.pictogram p
            WHERE s.author = :authorId
        ');
        $query->setParameter('authorId', $user->getId()); 
        $data['user_sequences'] = $query->getResult();

        $query = $this->doctrine->getManager()->createQuery('
            SELECT s, sp, p
            FROM App\Entity\Sequence s
            LEFT JOIN s.sequencePictograms sp
            LEFT JOIN sp.pictogram p
            WHERE s.author <> :authorId AND s.public = 1
        ');
        $query->setParameter('authorId', $user->getId()); 
        $data['public_sequences'] = $query->getResult();


		return $this->render('admin/dates/parts/select-sequence.html.twig', $data);
	}
	
}
