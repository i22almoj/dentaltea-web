<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvents;

class ChangePassType extends AbstractType{
	
	public function buildForm(FormBuilderInterface $builder, array $options){
		$builder->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'required' => true,
            'invalid_message' => 'Las contraseñas no coinciden.',
            'options' => ['attr' => ['class' => 'form-control toggle-password', 'placeholder' => 'Contraseña']],
            'first_options'  => ['label' => 'Contraseña *'],
            'second_options' => ['label' => 'Repetir contraseña *'],
        ])
        ->add('submit', SubmitType::class, array(
			'label' => 'Cambiar contraseña',
            'attr' => ['class' => 'btn btn-primary mt-3 mb-5']
		));
	}
	
}