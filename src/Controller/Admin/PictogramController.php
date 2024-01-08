<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Pictogram;
use App\Form\Type\PictogramType;
use Symfony\Component\Form\FormError;

class PictogramController extends BaseController
{
	private $repository;
	private $doctine;
	
	
	public function __construct(ManagerRegistry $doctrine){
		$this->doctrine = $doctrine;
		$this->repository = $doctrine->getRepository(Pictogram::class);
	}

/* 
	name: index
	description: Controls access to the pictograms list in the admin section
	path: /admin/pictogramas
*/		
    public function index(UserInterface $currentUser): Response
    {  
        if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');
		
		$pictograms = $this->getList(array('p_size' => 12));
		
		return $this->render('admin/pictograms/list.html.twig', [
			'pictograms' => $pictograms,
			'list' => $this->list
        ]);
    }

/* 
	name: listQuery
	description: Performs the query for the paginated list
*/	
	public function listQuery($pagination=true): Array{
		return $this->repository->listPictograms($this->list, $pagination);
	}

/* 
	name: edit
	description: Controls access to the pictogram edit form  in the admin section
	path: /admin/pictogramas/editar/{id}
*/
	public function edit($id, Request $request, UserInterface $currentUser, SluggerInterface $slugger): Response
	{
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');

		$pictogram = $this->repository->findOneby(array('id' => intval($id)));
		$pictogram_image = $pictogram->getImage();
		if(empty($pictogram_image)) $pictogram_image = null;

		if(empty($pictogram)) 
			return $this->redirectToRoute('admin_pictograms');
	
		$form = $this->createForm(PictogramType::class, $pictogram);
		
		$form->handleRequest($request);
		
		if($form->isSubmitted()){
			if($form->get('image')->getData()){
				$image = $this->uploadImage($form, $request, $slugger); 
			}else{
				if($form->get('change_image')->getData()==0){
					$image = $pictogram_image;
				}else{
					$image = null;
				}
			}

			if(empty($image)){
				$form->get('image')->addError(new FormError('Este campo es obligatorio.'));
			}else{
				$pictogram->setImage($image);
			}

			if($form->isValid()){
			
				$em = $this->getDoctrine()->getManager();			
				$em->persist($pictogram);
				$em->flush();
	
				$this->addFlash(
					'notice',
					'Cambios guardados correctamente.'
				);
	
				return $this->redirectToRoute('admin_pictograms');
			}
			
		}
		
		
		return $this->render('admin/pictograms/edit.html.twig', [
			'form' => $form->createView(),
			'pictogram' => $pictogram
        ]);
	}

/* 
	name: new
	description: Controls access to pictogram creation form in the admin section
	path: /admin/pictogramas/nuevo
*/		
	public function new(Request $request, UserInterface $currentUser, SluggerInterface $slugger): Response
	{	
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('admin_pictograms');

		$pictogram = new Pictogram();
		$pictogram->setCreationTime(new \Datetime('now'));
		$form = $this->createForm(PictogramType::class, $pictogram);
		
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid() ) {
			
			$pictogram->setCreationTime(new \Datetime('now'));
			$image = $this->uploadImage($form, $request, $slugger); 
			
			if(!empty($image)) $pictogram->setImage($image);
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($pictogram);
			$em->flush();

			$this->addFlash(
                'notice',
                'Pictograma creado correctamente.'
            );

			return $this->redirectToRoute('admin_pictograms');
		}

		return $this->render('admin/pictograms/new.html.twig', [
			'form' => $form->createView()
        ]);
	}

/* 
	name: delete
	description: Controls access to delete a pictogram in the admin section
	path: /admin/pictogramas/eliminar/{id}
*/			
	public function delete($id, UserInterface $currentUser, Request $request): Response
	{
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');

		$pictogram = $this->repository->findOneby(array('id' => intval($id)));

		if(empty($pictogram)) return $this->redirectToRoute('admin_pictograms');
		
		$this->deleteImage($pictogram->getImage());

		$this->doctrine->getManager()->remove($pictogram);
		$this->doctrine->getManager()->flush();

		$this->addFlash(
			'notice',
			'Pictograma eliminado correctamente.'
		);

		return $this->redirect($request->headers->get('referer'));
	}
	
/* 
	name: uploadImage
	description: Uploads an image to the server to be associated with a pictogram
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
