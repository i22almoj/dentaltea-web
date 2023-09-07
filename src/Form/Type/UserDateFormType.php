<?php
namespace App\Form\Type;

use App\Entity\Date;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\CallbackTransformer;

class UserDateFormType extends AbstractType{
    
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

	public function buildForm(FormBuilderInterface $builder, array $options){
        $date = $builder->getData();
        $request = Request::createFromGlobals();
       
        $builder->add('submit', SubmitType::class, array(
			'label' => 'Guardar cambios',
            'attr' => ['class' => 'btn btn-primary  mb-1'],
            'priority' => 0
		))
        ->add('author_id', HiddenType::class, array(
            'attr' => ['value' => (!empty($date->getAuthor())) ? $date->getAuthor()->getId() : "", 'class' => 'input_date_author_id'],
            'mapped' => false
        ))
        ->add('dateTime', DateTimeType::class, [
            'label' => 'Fecha y hora *',
            'widget' => 'single_text',
            'html5' => true,
            'attr' => ['class' => 'dateTimeField'],
        ])
		->add('description', TextareaType::class, array(
			'label' => 'Descripción *',
            'required' => true,
            'attr' => ['placeholder' => 'Descripción', 'class' => 'mb-5', 'rows' => 5],
            'priority' => 0
        ))->add('notificationsMobile', CheckboxType::class, [
            'label'    => 'Notificaciones en tu movil',
            'required' => false,
            'attr' => ['class' => 'switchery switchery-small', 'data-toggle' => 'switchery', 'data-switchery' => "true", 'data-color' => "rgb(91, 189, 219)"],
        ])->add('notificationsEmail', CheckboxType::class, [
            'label'    => 'Notificaciones por email',
            'required' => false,
            'attr' => ['class' => 'switchery switchery-small', 'data-toggle' => 'switchery', 'data-switchery' => "true", 'data-color' => "rgb(91, 189, 219)"],
        ])->add('sequence_id', HiddenType::class, array(
            'attr' => ['value' => (!empty($date->getSequence())) ? $date->getSequence()->getId() : "", "class" => "input_date_sequence_id"],
            'mapped' => false
        ));

        $builder->get('notificationsMobile')
        ->addModelTransformer(new CallbackTransformer(
         function ($notificationsMobileAsString) {
             return (bool)(int)$notificationsMobileAsString;
         },
         function ($notificationsMobileAsBoolean) {
             return (string)(int)$notificationsMobileAsBoolean;
         }
        ));

        $builder->get('notificationsEmail')
        ->addModelTransformer(new CallbackTransformer(
         function ($notificationsEmailAsString) {
             return (bool)(int)$notificationsEmailAsString;
         },
         function ($notificationsEmailAsBoolean) {
             return (string)(int)$notificationsEmailAsBoolean;
         }
        ));

		
	}

	
}