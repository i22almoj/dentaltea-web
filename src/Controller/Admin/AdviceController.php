<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Advice;
use App\Form\Type\AdviceType;

class AdviceController extends BaseController
{
    private $repository;
	private $doctine;
	
	public function __construct(ManagerRegistry $doctrine){
		$this->doctrine = $doctrine;
		$this->repository = $doctrine->getRepository(Advice::class);
	}

/* 
	name: index
	description: Controls access to the advices list in the admin section
	path: /admin/consejos
*/	
    public function index(UserInterface $currentUser): Response
    {  
        if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');
		
		$advices = $this->getList(array('p_size' => 12));
		
		$args = [
			'advices' => $advices,
			'list' => $this->list
        ];

		return $this->render('admin/advices/list.html.twig', $args);
    }

/* 
	name: listQuery
	description: Performs the query for the paginated list
*/	
	public function listQuery($pagination=true): Array{
		return $this->repository->listAdvices($this->list, $pagination);
	}

/* 
	name: edit
	description: Controls access to the advice edit form  in the admin section
	path: /admin/consejos/editar/{id}
*/
	public function edit($id, Request $request, UserInterface $currentUser, SluggerInterface $slugger): Response
	{
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');

		$advice = $this->repository->findOneby(array('id' => intval($id)));
		
		if(empty($advice)) 
			return $this->redirectToRoute('admin_advices');
	
		$form = $this->createForm(AdviceType::class, $advice);
		
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid()){
			
			$advice->setCreationTime(new \Datetime('now'));
			$delete_image = $form->get('delete_image')->getData();
			$image = $this->uploadImage($form, $request, $slugger); 

            if(!empty($image)) $advice->setImage($image);
			else if($delete_image){ 
				
				if(!empty($image = $advice->getImage())){
					$this->deleteImage($image);
				}				
				$advice->setImage(null);
			}
			
            $em = $this->getDoctrine()->getManager();			
			$em->persist($advice);
			$em->flush();

            $this->addFlash(
                'notice',
                'Cambios guardados correctamente.'
            );

			return $this->redirectToRoute('admin_advices');
		}
		
		return $this->render('admin/advices/edit.html.twig', [
			'form' => $form->createView(),
			'advice' => $advice,
        ]);
	}

/* 
	name: new
	description: Controls access to advice creation form in the admin section
	path: /admin/consejos/nuevo
*/	
	public function new(Request $request, UserInterface $currentUser, SluggerInterface $slugger): Response
	{	
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('admin_advices');

		$advice = new Advice();
		$advice->setCreationTime(new \Datetime('now'));
		$form = $this->createForm(AdviceType::class, $advice);
		
		$form->handleRequest($request);
		
		$back  = $request->headers->get('referer');

		if($form->isSubmitted() && $form->isValid() ) {
			
			$advice->setCreationTime(new \Datetime('now'));
			$image = $this->uploadImage($form, $request, $slugger); 
			
			$advices = $this->repository->listAdvices(array('filter' => array()), false);
			if(empty($advices)) $sortNumber = 1;
			else $sortNumber = sizeof($advices)+1;

			$advice->setSortNumber($sortNumber);

			if(!empty($image)) $advice->setImage($image);
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($advice);
			$em->flush();

			$this->addFlash(
                'notice',
                'Advicea creado correctamente.'
            );

			return $this->redirectToRoute('admin_advices');
		}

        return $this->render('admin/advices/new.html.twig', [
			'form' => $form->createView()
        ]);
	}

/* 
	name: delete
	description: Controls access to delete an advice in the admin section
	path: /admin/consejos/eliminar/{id}
*/		
	public function delete($id, UserInterface $currentUser, Request $request): Response
	{
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');

		$advice = $this->repository->findOneby(array('id' => intval($id)));

		if(empty($advice)) return $this->redirectToRoute('admin_advices');
		
		if(!empty($image = $advice->getImage()))
			$this->deleteImage($image);

		$this->doctrine->getManager()->remove($advice);
		$this->doctrine->getManager()->flush();

		$advice = $this->repository->updateOrder();
		
		$this->addFlash(
			'notice',
			'Advicea eliminado correctamente.'
		);

		return $this->redirect($request->headers->get('referer'));
	}

/* 
	name: moveUp
	description: Moves up an advice in the order in which they will be displayed
	path: /admin/consejos/subir/{id}
*/	
	public function moveUp($id, Request $request): Response
    {  
        $this->repository->adviceMoveUp($id);

		return $this->redirectToRoute('admin_advices');
    }

/* 
	name: moveDown
	description: Moves down an advice in the order in which they will be displayed
	path: /admin/consejos/bajar/{id}
*/		
	public function moveDown($id, Request $request): Response
    {  
        $this->repository->adviceMoveDown($id);

		return $this->redirectToRoute('admin_advices');
    }

/* 
	name: uploadImage
	description: Uploads an image to the server to be associated with an advice
*/		
	private function uploadImage($form, $request, SluggerInterface $slugger){
		$imageFile = $form->get('image')->getData();

		if ($imageFile) {
			$originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
			$safeFilename = $slugger->slug($originalFilename);
			$uploads_dir = $this->getParameter('uploads_directory');
			if(!file_exists($uploads_dir)) mkdir($uploads_dir);
			$base_url = $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
			$base_path = $this->getParameter('kernel.project_dir').'/public';
			$uploads_url = str_replace($base_path, $base_url, $uploads_dir);
			$uploads_uri = substr(str_replace($base_path, '', $uploads_dir), 1);
			$newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

			try {
				$imageFile->move(
					$uploads_dir,
					$newFilename
				);
			} catch (FileException $e) {
				// ... handle exception if something happens during file upload
			}

			return $uploads_uri.'/'.$newFilename;
		}else{
			return null;
		}
	}

/* 
	name: deleteImage
	description: Deletes an image
*/		
	private function deleteImage($image){
		$path = $this->getParameter('kernel.project_dir').'/public/'.$image;

		if(file_exists($path)){
			unlink($path);
			return true;
		}

		return false;
	}
}
