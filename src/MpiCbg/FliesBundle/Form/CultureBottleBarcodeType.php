<?php

namespace MpiCbg\FliesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CultureBottleBarcodeType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('parentBarcode', 'number', array('required' => false))
                ->add('entity', new CultureBottleType());
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'MpiCbg\FliesBundle\Wrapper\Barcode\CultureBottle',
        );
    }
}

?>