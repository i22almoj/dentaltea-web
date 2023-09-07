<?php
namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
use Symfony\Component\Form\CallbackTransformer;

class UserEditType extends AbstractType{
    
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

	public function buildForm(FormBuilderInterface $builder, array $options){
        $user = $builder->getData();
       
        $builder->add('active', CheckboxType::class, [
            'label'    => 'Cuenta activa',
            'required' => false,
            'attr' => ['class' => 'switchery switchery-small', 'data-toggle' => 'switchery', 'data-switchery' => "true", 'data-color' => "rgb(91, 189, 219)"],
        ])
        ->add('role', ChoiceType::class, array(
			'label' => 'Rol *',
            'required' => true,
            'attr' => ['class' => 'select2'],
            'choices' => array('Usuario común' => 'ROLE_USER', 'Administrador' => 'ROLE_ADMIN'),
            'priority' => 1
		))
        ->add('name', TextType::class, array(
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

        $builder->get('active')
            ->addModelTransformer(new CallbackTransformer(
            function ($activeAsString) {
                // transform the string to boolean
                return (bool)(int)$activeAsString;
            },
            function ($activeAsBoolean) {
                // transform the boolean to string
                return (string)(int)$activeAsBoolean;
            }
        ));
	}

	
}