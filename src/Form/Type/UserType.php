<?php
namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UserType extends AbstractType{
    
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
	public function buildForm(FormBuilderInterface $builder, array $options){
        $user = $builder->getData();
        
        $builder->add('name', TextType::class, array(
			'label' => 'Nombre *',
            'required' => true,
            'attr' => ['placeholder' => 'Nombre'],
            'priority' => 100
		))
		->add('email', EmailType::class, array(
			'label' => 'Correo electrónico *',
            'required' => true,
            'attr' => ['placeholder' => 'Correo electrónico'],
            'priority' => 100
		))
        ->add('submit', SubmitType::class, array(
			'label' => 'Guardar',
            'attr' => ['class' => 'btn btn-primary mt-3 mb-5'],
            'priority' => 0
		));
	}

	
}