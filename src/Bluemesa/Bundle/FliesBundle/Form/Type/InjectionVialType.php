<?php

/*
 * Copyright 2013 Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Bluemesa\Bundle\FliesBundle\Form\Type;

use Bluemesa\Bundle\CoreBundle\Form\EntityTypeaheadType;
use Bluemesa\Bundle\CoreBundle\Form\TextEntityType;
use Bluemesa\Bundle\FormsBundle\Form\Type\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * InjectionVialSimpleType class
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class InjectionVialType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('setupDate', DatePickerType::class, array(
                        'label' => 'Injection date',
                        'horizontal' => true
                    )
                )
                ->add('storedFlipDate', DatePickerType::class, array(
                        'label'      => 'Check date',
                        'required'   => false,
                        'horizontal' => true
                    )
                )
                ->add('injectionType', ChoiceType::class, array(
                        'choices'    => array(
                            'phiC31'      => 'phiC31',
                            'P-element'   => 'P-element',
                            'piggyBac'    => 'piggyBac',
                            'Minos'       => 'Minos',
                            'phiC31 RMCE' => 'phiC31 RMCE',
                            'Flp RMCE'    => 'Flp RMCE',
                            'Cre RMCE'    => 'Cre RMCE'
                        ),
                        'label'      => 'Injection type',
                        'horizontal' => true
                    )
                )
                ->add('constructName', TextType::class, array(
                        'label'      => 'Construct name',
                        'required'   => true,
                        'horizontal' => true
                    )
                )
                ->add('targetStock', EntityTypeaheadType::class, array(
                        'choice_label' => 'name',
                        'class'        => 'BluemesaFliesBundle:Stock',
                        'label'        => 'Target stock',
                        'required'     => false,
                        'horizontal'   => true
                    )
                )
                ->add('targetStockVial', TextEntityType::class, array(
                        'choice_label'        => 'id',
                        'class'               => 'BluemesaFliesBundle:StockVial',
                        'format'              => '%06d',
                        'required'            => false,
                        'label'               => 'Target stock source vial',
                        'attr'                => array('class' => 'barcode'),
                        'widget_addon_append' => array('icon' => 'qrcode'),
                        'horizontal'          => true
                    )
                )
                ->add('embryoCount', NumberType::class, array(
                        'label'      => 'Embryo count',
                        'horizontal' => true
                    )
                )
                ->add('vendor', TextType::class, array(
                        'label'      => 'Vendor',
                        'required'   => false,
                        'horizontal' => true
                    )
                )
                ->add('receiptDate', DatePickerType::class, array(
                        'label'      => 'Receipt date',
                        'required'   => false,
                        'horizontal' => true
                    )
                )
                ->add('orderNo', TextType::class, array(
                        'label'      => 'Order number',
                        'required'   => false,
                        'horizontal' => true
                    )
                )
                ->add('notes', TextareaType::class, array(
                        'label'      => 'Notes',
                        'required'   => false,
                        'horizontal' => true
                    )
                );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                 'inherit_data' => true
            )
        );
    }
}
