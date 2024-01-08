<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\Type\UserEditType;
use App\Form\Type\UserNewType;
use App\Form\Type\ChangePassType;


class UserController extends BaseController
{
	private $repository;
	private $doctine;
	
	public function __construct(ManagerRegistry $doctrine){
		$this->doctrine = $doctrine;
		$this->repository = $doctrine->getRepository(User::class);
	}

/* 
	name: index
	description: Controls access to the users list in the admin section
	path: /admin/usuarios
*/		
    public function index(UserInterface $currentUser): Response
    {  
        if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');
		
		$users = $this->getList(array(
			'filter_fields' => array('role')
		));

		return $this->render('admin/users/list.html.twig', [
			'users' => $users,
			'list' => $this->list
        ]);
    }

/* 
	name: listQuery
	description: Performs the query for the paginated list
*/		
	public function listQuery($pagination=true): Array{ 
		return $this->repository->listUsers($this->list, $pagination);
	}

/* 
	name: admin
	description: Redirects to /admin/usuarios
	path: /admin
*/			
	public function admin(UserInterface $currentUser): Response
	{
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');

		return $this->redirectToRoute('admin_users');
	}

/* 
	name: edit
	description: Controls access to the user edit form  in the admin section
	path: /admin/usuarios/editar/{id}
*/	
	public function edit($id, Request $request, UserPasswordHasherInterface $hasher, UserInterface $currentUser): Response
	{
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');

		$user = $this->repository->findOneby(array('id' => intval($id)));
		if(empty($user)) 
			return $this->redirectToRoute('home');
	
		$form = $this->createForm(UserEditType::class, $user);
		
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid()){
			
			$em = $this->getDoctrine()->getManager();			
			$em->persist($user);
			$em->flush();

            $this->addFlash(
                'notice',
                'Cambios guardados correctamente.'
            );

			return $this->redirectToRoute('admin_users');
		}
		
		$form_pass = $this->createForm(ChangePassType::class, $user);
		$form_pass->handleRequest($request);
		if($form_pass->isSubmitted() && $form_pass->isValid()){
			
			$hashed = $hasher->hashPassword($user, $user->getPassword());
			$user->setPassword($hashed);
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();
			
            $this->addFlash(
                'notice_pass',
                'Contraseña cambiada correctamente.'
            );
		}

        return $this->render('admin/users/edit.html.twig', [
			'form' => $form->createView(),
			'form_pass' => $form_pass->createView(),
			'user' => $user
        ]);
	}

/* 
	name: new
	description: Controls access to user account creation form in the admin section
	path: /admin/usuarios/nuevo
*/		
	public function new(Request $request, UserPasswordHasherInterface $hasher, UserInterface $currentUser): Response
	{	
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');

		$user = new User();
		$user->setCreationTime(new \Datetime('now'));
		$form = $this->createForm(UserNewType::class, $user);
		
		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid() ) {
			
			$user->setCreationTime(new \Datetime('now'));

			// Cifrar contraseña
			$hashed = $hasher->hashPassword($user, $user->getPassword());
			$user->setPassword($hashed);
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();

			$this->addFlash(
                'notice',
                'Usuario creado correctamente.'
            );

			return $this->redirectToRoute('admin_users');
		}
		
        return $this->render('admin/users/new.html.twig', [
			'form' => $form->createView()
        ]);
	}

/* 
	name: delete
	description: Controls access to delete an user in the admin section
	path: /admin/usuarios/eliminar/{id}
*/		
	public function delete($id, UserInterface $currentUser, Request $request): Response
	{
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');

		$user = $this->repository->findOneby(array('id' => intval($id)));
		if(empty($user)) return $this->redirectToRoute('admin_users');

		$this->doctrine->getManager()->remove($user);
		$this->doctrine->getManager()->flush();

		$this->addFlash(
			'notice',
			'Usuario eliminado correctamente.'
		);

		return $this->redirect($request->headers->get('referer'));
	}
	
}
