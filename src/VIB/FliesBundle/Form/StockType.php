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

namespace VIB\FliesBundle\Form;

use Bluemesa\Bundle\CoreBundle\Form\TextEntityType;
use Bluemesa\Bundle\FormsBundle\Form\Type\TypeaheadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VIB\FliesBundle\Form\Type\GenotypeType;

/**
 * StockType class
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class StockType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
                        'label'      => 'Name',
                        'horizontal' => true
                    )
                )
                ->add('genotype', GenotypeType::class, array(
                        'label'      => 'Genotype',
                        'attr'       => array(
                            'data-id-source' => 'cross-id',
                            'class'          => 'fb-genotype'
                        ),
                        'data_route' => 'vib_flies_ajax_genotypes',
                        'required'   => false,
                        'horizontal' => true
                    )
                )
                ->add('source_cross', TextEntityType::class, array(
                        'choice_label'        => 'id',
                        'class'               => 'VIBFliesBundle:CrossVial',
                        'format'              => '%06d',
                        'required'            => false,
                        'label'               => 'Source cross',
                        'attr'                => array('class' => 'barcode cross-id'),
                        'widget_addon_append' => array('icon' => 'qrcode'),
                        'horizontal'          => true
                    )
                )
                ->add('notes', TextareaType::class, array(
                        'label'      => 'Notes',
                        'required'   => false,
                        'horizontal' => true
                    )
                )
                ->add('vendor', TypeaheadType::class, array(
                        'label'      => 'Vendor',
                        'required'   => false,
                        'attr'       => array('class' => 'fb-vendor'),
                        'data_route' => 'vib_flies_ajax_flybasevendor',
                        'horizontal' => true
                    )
                )
                ->add('vendorId', TypeaheadType::class, array(
                        'label'      => 'Vendor ID',
                        'required'   => false,
                        'attr'       => array('class' => 'fb-vendorid'),
                        'data_route' => 'vib_flies_ajax_flybasestock',
                        'horizontal' => true
                    )
                )
                ->add('infoURL', UrlType::class, array(
                        'label'      => 'Info URL',
                        'required'   => false,
                        'attr'       => array(
                            'placeholder' => 'Paste address here',
                            'class'       => 'fb-link'
                        ),
                        'horizontal' => true
                    )
                )
                ->add('verified', CheckboxType::class, array(
                        'label'      => '',
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
            'data_class' => 'VIB\FliesBundle\Entity\Stock'
        ));
    }
}
