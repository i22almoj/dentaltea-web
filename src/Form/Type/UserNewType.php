<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\CallbackTransformer;

class UserNewType extends AbstractType{

    private $doctrine;
    private $currentUser;

    public function __construct(ManagerRegistry $doctrine, Security $security)
    {
        $this->doctrine = $doctrine;
        $this->currentUser = $security->getUser();
    }
	
	public function buildForm(FormBuilderInterface $builder, array $options){
        $user = $builder->getData();
        
        $builder->add('active', CheckboxType::class, [
            'label'    => 'Cuenta activa',
            'required' => false,
            'attr' => ['class' => 'switchery switchery-small', 'data-toggle' => 'switchery', 'data-switchery' => "true", 'data-color' => "rgb(91, 189, 219)"]            
        ])
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
        ->add('role', ChoiceType::class, array(
			'label' => 'Rol *',
            'required' => true,
            'attr' => ['class' => 'select2'],
            'choices' => array('Usuario común' => 'ROLE_USER', 'Administrador' => 'ROLE_ADMIN'),
            'priority' => 1
		))
		->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'required' => true,
            'invalid_message' => 'Las contraseñas no coinciden.',
            'options' => ['attr' => ['class' => 'form-control toggle-password']],
            'first_options'  => ['label' => 'Contraseña *'],
            'second_options' => ['label' => 'Repetir contraseña *'],
        ])
		->add('submit', SubmitType::class, array(
			'label' => 'Crear cuenta',
            'attr' => ['class' => 'btn btn-primary mt-3']
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