<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Sequence;
use App\Entity\SequencePictogram;
use App\Entity\Pictogram;
use App\Entity\User;
use App\Form\Type\SequenceType;
use App\Repository\SequenceRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class SequenceController extends BaseController
{
	private $repository;
	private $doctine;
	
	public function __construct(ManagerRegistry $doctrine){
		$this->doctrine = $doctrine;
		$this->repository = [];
		$this->repository['sequence'] = $doctrine->getRepository(Sequence::class);
		$this->repository['pictogram'] = $doctrine->getRepository(Pictogram::class);
		$this->repository['user'] = $doctrine->getRepository(User::class);
		parent::__construct();
	}

/* 
	name: index
	description: Controls access to the sequences list in the admin section
	path: /admin/secuencias
*/		
    public function index(UserInterface $currentUser): Response
    {  
        if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');
		
		$sequences = $this->getList(array('p_size' => 12, 'filter_fields' => ['author']));
		$authors = $this->repository['user']->findAllOrderedByName();
		return $this->render('admin/sequences/list.html.twig', [
			'sequences' => $sequences,
			'list' => $this->list,
			'authors' => $authors
        ]);
    }

/* 
	name: listQuery
	description: Performs the query for the paginated list
*/		
	public function listQuery($pagination=true): Array{
		return $this->repository['sequence']->listSequences($this->list, $pagination);
	}

/* 
	name: edit
	description: Controls access to the sequence edit form  in the admin section
	path: /admin/secuencias/editar/{id}
*/	
	public function edit($id, Request $request, UserInterface $currentUser): Response
	{
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');

		$sequence = $this->repository['sequence']->findOneby(array('id' => intval($id)));
		
		if(empty($sequence)) 
			return $this->redirectToRoute('admin_sequences');
	
		$form = $this->createForm(SequenceType::class, $sequence);
		
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

			return $this->redirectToRoute('admin_sequences');
		}

		$sequencePictograms = $sequence->getSequencePictograms();
		if($form->isSubmitted() && !$form->isValid()){
			$sequencePictograms = array();
		}
		
		return $this->render('admin/sequences/edit.html.twig', [
			'form' => $form->createView(),
			'sequence' => $sequence,
			'pictograms' => $this->repository['pictogram']->listPictograms(array('orderby' => 'creationTime'), false),
            'sequencePictograms' => $sequencePictograms
        ]);
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

/* 
	name: new
	description: Controls access to sequence creation form in the admin section
	path: /admin/secuencias/nuevo
*/	
	public function new(Request $request, UserInterface $currentUser): Response
	{	
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('admin_sequences');
		
		$sequence = new Sequence($currentUser);
		$sequence->setCreationTime(new \Datetime('now'));
		$form = $this->createForm(SequenceType::class, $sequence);
		
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid() ) {
			
			$sequence->setCreationTime(new \Datetime('now'));
			$sequence->setAuthor($currentUser);
			$manager = $this->doctrine->getManager();
			$manager->persist($sequence);
			$manager->flush();

			$sort = json_decode($form->get('pictograms')->getData());
			$this->sortPictograms($sequence, $sort);

            $this->addFlash(
                'notice',
                'Secuencia creada correctamente.'
            );

			return $this->redirectToRoute('admin_sequences');
		}

		return $this->render('admin/sequences/new.html.twig', [
			'form' => $form->createView(),
			'pictograms' => $this->repository['pictogram']->listPictograms(array('orderby' => 'creationTime'), false)
        ]);
	}

/* 
	name: delete
	description: Controls access to delete a sequence in the admin section
	path: /admin/secuencias/eliminar/{id}
*/		
	public function delete($id, UserInterface $currentUser, Request $request): Response
	{
		if($currentUser->getRole()!='ROLE_ADMIN')
			return $this->redirectToRoute('home');

		$sequence = $this->repository['sequence']->findOneBy(array('id' => intval($id)));

		if(empty($sequence)) return $this->redirectToRoute('admin_sequences');
		
		$this->doctrine->getManager()->remove($sequence);
		$this->doctrine->getManager()->flush();

		$this->addFlash(
			'notice',
			'Secuencia eliminado correctamente.'
		);

		return $this->redirect($request->headers->get('referer'));
	}
}
