<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Doctrine\Persistence\ManagerRegistry;

// debug
use Psr\Log\LoggerInterface;

class RegisterType extends AbstractType{

    private $doctrine;
    private $logger;

    public function __construct(ManagerRegistry $doctrine, LoggerInterface $logger)
    {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
    }

	
	public function buildForm(FormBuilderInterface $builder, array $options){


        $builder->add('name', TextType::class, array(
			'label' => 'Nombre *',
            'required' => true,
		))
		->add('email', EmailType::class, array(
			'label' => 'Correo electrónico *',
            'required' => true,
		))
        ->add('role', HiddenType::class, array('empty_data' => 'ROLE_USER'))
		->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'required' => true,
            'invalid_message' => 'Las contraseñas no coinciden.',
            'options' => ['attr' => ['class' => 'form-control toggle-password white-toggle']],
            'first_options'  => ['label' => 'Contraseña *'],
            'second_options' => ['label' => 'Repetir contraseña *'],
        ])
        ->add('privacy', CheckboxType::class, [
            'label' => 'He leído y acepto la <a href="#" data-bs-toggle="modal" data-bs-target="#modal-privacy">Política de privacidad</a>',
            'label_html' => true,
            'row_attr' => ['class' => 'mb-3 px-3 privacy-check'],
            'mapped' => false, // Este campo no se mapea a una propiedad de entidad
            'required' => true, // Puedes configurarlo como true o false según tus necesidades
        ])
		->add('submit', SubmitType::class, array(
			'label' => 'Registrarse',
            'attr' => ['class' => 'btn btn-primary mt-4']
		));
	}

}