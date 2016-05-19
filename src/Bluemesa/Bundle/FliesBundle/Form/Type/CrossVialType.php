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

use Bluemesa\Bundle\CoreBundle\Form\TextEntityType;
use Bluemesa\Bundle\FormsBundle\Form\Type\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CrossVialType class
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class CrossVialType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('setupDate', DatePickerType::class, array(
                        'label'      => 'Setup date',
                        'horizontal' => true
                    )
                )
                ->add('storedFlipDate', DatePickerType::class, array(
                        'label'      => 'Check date',
                        'required'   => false,
                        'horizontal' => true
                    )
                )
                ->add('virgin', TextEntityType::class, array(
                        'choice_label'        => 'id',
                        'class'               => 'BluemesaFliesBundle:Vial',
                        'format'              => '%06d',
                        'label'               => 'Virgin vial',
                        'attr'                => array('class' => 'barcode virgin-id'),
                        'widget_addon_append' => array('icon' => 'qrcode'),
                        'horizontal'          => true
                    )
                )
                ->add('virginName', GenotypeType::class, array(
                        'label'      => 'Virgin genotype',
                        'attr'       => array('data-id-source' => 'virgin-id'),
                        'data_route' => 'bluemesa_flies_ajax_genotypes',
                        'required'   => false,
                        'horizontal' => true
                    )
                )
                ->add('male', TextEntityType::class, array(
                        'choice_label'        => 'id',
                        'class'               => 'BluemesaFliesBundle:Vial',
                        'format'              => '%06d',
                        'label'               => 'Male vial',
                        'attr'                => array('class' => 'barcode male-id'),
                        'widget_addon_append' => array('icon' => 'qrcode'),
                        'horizontal'          => true
                    )
                )
                ->add('maleName', GenotypeType::class, array(
                        'label'      => 'Male genotype',
                        'attr'       => array('data-id-source' => 'male-id'),
                        'data_route' => 'bluemesa_flies_ajax_genotypes',
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
