<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Entity\Sequence;
use App\Entity\SequencePictogram;
use App\Entity\Pictogram;
use App\Form\Type\UserSequenceType;
use App\Form\Type\UserAdminSequenceType;
use App\Repository\SequenceRepository;


class SequenceController extends AbstractController
{
    private $repository;
	private $doctine;
	
	public function __construct(ManagerRegistry $doctrine){
		$this->doctrine = $doctrine;
        $this->repository = [];
		$this->repository['sequence'] = $doctrine->getRepository(Sequence::class);
		$this->repository['pictogram'] = $doctrine->getRepository(Pictogram::class);
	}

/* 
	name: index
	description: Controls access to the user's pictogram sequence list
	path: /apoyos-visuales
*/
    public function index(UserInterface $currentUser): Response
    {  
        $sequences = $this->repository['sequence']->findBy(['author' => $currentUser], ['id' => 'ASC']);

		$publicSequences = $this->repository['sequence']->listPublicSequences($currentUser);
		
		return $this->render('user/sequences/list.html.twig', [
			'list' => $sequences,
			'public' => $publicSequences
        ]);
    }

/* 
	name: edit
	description: Controls access to the pictogram sequence edit form
	path: /apoyos-visuales/editar/{id}
*/	
	public function edit($id, Request $request, UserInterface $currentUser): Response
	{

		$sequence = $this->repository['sequence']->findOneby(array('id' => intval($id)));

		if(empty($sequence)||($currentUser->getRole()!='ROLE_ADMIN'&&$sequence->getAuthor()->getId()!=$currentUser->getId())) 
			return $this->redirectToRoute('sequences');
		
		$form = $this->createForm(UserSequenceType::class, $sequence);
		
		$form->handleRequest($request);
		$pictograms = $this->repository['pictogram']->listPictograms(array('orderby' => 'creationTime'), false);

		if($form->isSubmitted() && $form->isValid()){
			
			$this->doctrine->getManager()->persist($sequence);
			$this->doctrine->getManager()->flush();
			
			$sort = json_decode($form->get('pictograms')->getData());
			$this->sortPictograms($sequence, $sort);

            $this->addFlash(
                'notice',
                'Cambios guardados correctamente.'
            );

			return $this->redirectToRoute('sequences');
		}

		$sequencePictograms = $sequence->getSequencePictograms();
		if($form->isSubmitted() && !$form->isValid()){
			$sequencePictograms = array();
		}
		
		return $this->render('user/sequences/edit.html.twig', [
			'form' => $form->createView(),
			'sequence' => $sequence,
			'pictograms' => $this->repository['pictogram']->listPictograms(array('orderby' => 'creationTime'), false),
            'sequencePictograms' => $sequencePictograms
        ]);
	}

/* 
	name: new
	description: Controls access to the pictogram sequence creation form
	path: /apoyos-visuales/nuevo
*/		
	public function new(Request $request, UserInterface $currentUser): Response
	{	

		$sequence = new Sequence($currentUser);
		$sequence->setCreationTime(new \Datetime('now'));
		$form = $this->createForm(UserSequenceType::class, $sequence);
		
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid() ) {
			
			$sequence->setCreationTime(new \Datetime('now'));
			$sequence->setAuthor($currentUser);
			if($currentUser->getRole()=='ROLE_USER'){
				$sequence->setPublic(0);
			}
			$manager = $this->doctrine->getManager();
			$manager->persist($sequence);
			$manager->flush();

			$sort = json_decode($form->get('pictograms')->getData());
			$this->sortPictograms($sequence, $sort);

            $this->addFlash(
                'notice',
                'Apoyo visual creado correctamente.'
            );

			return $this->redirectToRoute('sequences');
		}

		return $this->render('user/sequences/new.html.twig', [
			'form' => $form->createView(),
			'pictograms' => $this->repository['pictogram']->listPictograms(array('orderby' => 'creationTime'), false)
        ]);
	}

/* 
	name: delete
	description: Controls access to delete a pictogram sequence
	path: /apoyos-visuales/eliminar/{id}
*/		
	public function delete($id, UserInterface $currentUser, Request $request): Response
	{
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');

		$sequence = $this->repository['sequence']->findOneBy(array('id' => intval($id)));

		if(empty($sequence)) return $this->redirectToRoute('sequences');
		
		$this->doctrine->getManager()->remove($sequence);
		$this->doctrine->getManager()->flush();

		$this->addFlash(
			'notice',
			'Secuencia eliminado correctamente.'
		);

		return $this->redirect($request->headers->get('referer'));
	}

/* 
	name: sortPictograms
	description: Updates the order of the pictograms in a sequence
*/		
	private function sortPictograms($sequence, $sort){
		
		if(empty($sequence)||empty($sequence->getId())) return false;

		$this->clearPictograms($sequence);

		if(empty($sort)||!is_array($sort)) return true;
		
		foreach($sort as $item){ 
			$pictogram = $this->repository['pictogram']->findOneBy(array('id' =>intval($item->id)));
			if(empty($pictogram)) continue;
			
			$sortItem = new SequencePictogram();
			$sortItem->setSequence($sequence);
			$sortItem->setPictogram($pictogram);
			$sortItem->setDescription($item->description);
			$sortItem->setSortNumber(intval($item->sort_number));
			
			$this->doctrine->getManager()->persist($sortItem);
			$this->doctrine->getManager()->flush();
		}
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
}
