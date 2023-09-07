<?php
namespace App\Form\Type;

use App\Entity\Pictogram;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class PictogramType extends AbstractType{
    
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
	public function buildForm(FormBuilderInterface $builder, array $options){
        $pictogram = $builder->getData();
        $request = Request::createFromGlobals();
        $base_url = $request->getScheme() . '://'.$request->getHttpHost() . $request->getBasePath();

        $attr = ['class' => 'dropify', 'data-max-file-size' => '3M', 'data-width' => '500', 'data-height' => '400', 'data-allowed-file-extensions' => 'jpg jpeg png pneg gif tif tiff bmp webp svg', 'accept' => '.jpg, .jpeg, .png, .pneg, .gif, .tif, .tiff, .bmp, .webp, .svg'];

        if(!empty($pictogram)&&!empty($pictogram->getImage()))
        $attr['data-default-file'] = $base_url.'/'.$pictogram->getImage();

        $builder->add('image', FileType::class, array(
			'label' => 'Imagen de pictograma *',
            'attr' => $attr,
            'data_class' => null,
            'required' => false,
            'empty_data' => '',
            'mapped' => false,
            'row_attr' => ['class' => 'pictogram-image-group mb-3'],
            'constraints' => [
                new File([
                    'maxSize' => '3072k'
                ])
            ],
            'priority' => 0
		))
        ->add('change_image', HiddenType::class, array(
            'mapped' => false,
            'attr' => ['value' => '0']
        ))
        ->add('description', TextType::class, array(
			'label' => 'Descripción *',
            'required' => true,
            'attr' => ['placeholder' => 'Descripción'],
            'priority' => 0
		))
		->add('submit', SubmitType::class, array(
			'label' => 'Guardar',
            'attr' => ['class' => 'btn btn-primary mt-3 mb-5'],
            'priority' => 0
		));


	}

	
}