<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ResetPassType extends AbstractType{
    
 	public function buildForm(FormBuilderInterface $builder, array $options){
    
        $builder->add('email', EmailType::class, array(
			'label' => 'Introduce tu correo electrónico',
            'required' => true,
            'mapped' => false,
            'attr' => ['class' => 'mt-2'],
            'priority' => 100
		))
        ->add('submit', SubmitType::class, array(
			'label' => 'Continuar',
            'attr' => ['class' => 'btn btn-primary mt-3 mb-5'],
            'priority' => 0
		));
	}

	
}