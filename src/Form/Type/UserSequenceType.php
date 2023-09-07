<?php
namespace App\Form\Type;

use App\Entity\Sequence;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSequenceType extends AbstractType{
    
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

	public function buildForm(FormBuilderInterface $builder, array $options){
        $sequence = $builder->getData();
        $request = Request::createFromGlobals();
       
        $builder->add('submit', SubmitType::class, array(
			'label' => 'Guardar cambios',
            'attr' => ['class' => 'btn btn-primary  mb-1'],
            'priority' => 0
		))
		->add('description', TextType::class, array(
			'label' => 'Descripción *',
            'required' => true,
            'attr' => ['placeholder' => 'Descripción', 'class' => 'mb-5'],
            'priority' => 0
        ))->add('pictograms', HiddenType::class, array(
            'mapped' => false,
            'attr' => ['class' => 'sequence_pictograms_hidden_input'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Debes añadir al menos un pictograma',
                    'groups' => ['not_empty_group']
                ])
            ]
        ));
		
	}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => ['not_empty_group']
        ]);
    }

	
}