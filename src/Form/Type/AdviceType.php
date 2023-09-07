<?php
namespace App\Form\Type;

use App\Entity\Advice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class AdviceType extends AbstractType{
    
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
	public function buildForm(FormBuilderInterface $builder, array $options){
        $advice = $builder->getData();
        $request = Request::createFromGlobals();
        $base_url = $request->getScheme() . '://'.$request->getHttpHost() . $request->getBasePath();

        $attr = ['class' => 'dropify', 'data-max-file-size' => '3M', 'data-width' => '500', 'data-height' => '150', 'data-allowed-file-extensions' => 'jpg jpeg png pneg gif tif tiff bmp webp svg', 'accept' => '.jpg, .jpeg, .png, .pneg, .gif, .tif, .tiff, .bmp, .webp, .svg'];

        if(!empty($advice)&&!empty($advice->getImage()))
        $attr['data-default-file'] = $base_url.'/'.$advice->getImage();

        $builder
        ->add('title', TextType::class, array(
			'label' => 'Título *',
            'required' => true,
            'attr' => ['placeholder' => 'Título'],
            'priority' => 0
		))
        ->add('content', TextareaType::class, array(
			'label' => 'Contenido *',
            'required' => true,
            'attr' => [ 'rows' => 10, 'class' => 'd-none'],
            'row_attr' => ['class' => 'quill-wrapper mb-3'],
            'priority' => 0
		))->add('image', FileType::class, array(
			'label' => 'Imagen de consejo',
            'attr' => $attr,
            'data_class' => null,
            'required' => false,
            'empty_data' => '',
            'mapped' => false,
            'row_attr' => ['id' => 'editor', 'class' => 'advice-image-group mb-3'],
            'constraints' => [
                new File([
                    'maxSize' => '3072k'
                ])
            ],
            'priority' => 0
		))
        ->add('delete_image', HiddenType::class,  array('mapped' => false))
		->add('submit', SubmitType::class, array(
			'label' => 'Guardar',
            'attr' => ['class' => 'btn btn-primary mt-3 mb-5'],
            'priority' => 0
		));
	}

	
}