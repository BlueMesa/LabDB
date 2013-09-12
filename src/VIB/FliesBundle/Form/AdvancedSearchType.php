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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * AdvancedSearchType class
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class AdvancedSearchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "advanced_search_form";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('query', 'text', array(
                        'label' => 'Include terms',
                        'required' => false,
                        'attr' => array(
                          'class' => 'input-block-level',
                          'placeholder' => 'separate terms with space')))
                ->add('exclude', 'text', array(
                        'label' => 'Exclude terms',
                        'required' => false,
                        'attr' => array(
                          'class' => 'input-block-level',
                          'placeholder' => 'separate terms with space')))
                ->add('filter', 'choice', array(
                        'label' => 'Scope',
                        'choices'   => array(
                            'crossvial' => 'Crosses',
                            'injectionvial' => 'Injections'),
                        'expanded' => true,
                        'empty_value' => 'Stocks',
                        'empty_data' => 'stock',
                        'required' => false))
                ->add('options', 'choice', array(
                        'label' => 'Options',
                        'choices'   => array(
                            'private' => 'Only private',
                            'dead' => 'Include dead',
                            'notes' => 'Include comments'),
                        'expanded' => true,
                        'multiple' => true,
                        'required' => false));
    }
}