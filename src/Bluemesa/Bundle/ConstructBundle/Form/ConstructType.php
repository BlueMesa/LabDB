<?php

/*
 * This file is part of the ConstructBundle.
 *
 * Copyright (c) 2016 BlueMesa LabDB Contributors <labdb@bluemesa.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bluemesa\Bundle\ConstructBundle\Form;

use Bluemesa\Bundle\ConstructBundle\Entity\Construct;
use Bluemesa\Bundle\ConstructBundle\Entity\RestrictionLigation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ConstructType class
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class ConstructType extends AbstractType
{
    /**
     * @var array
     */
    private static $methods = array(
        'Restriction-Ligation' => array(
            'entity' => RestrictionLigation::class,
            'form' => RestrictionLigationType::class
        )
    );

    /**
     * @var array
     */
    private static $types = array(
        'Plasmid' => 'plasmid',
        'Fosmid' => 'fosmid',
        'BAC' => 'BAC',
        'Cosmid' => 'cosmid',
    );

    /**
     * @var array
     */
    private static $antibiotics = array(
        'Ampicillin' => 'Amp',
        'Blasticidin' => 'Bla',
        'Bleocin' => 'Ble',
        'Chroramphenicol'  => 'Cm',
        'Coumermycin' => 'Com',
        'D-cycloserine' => 'DCS',
        'Erythromycin' => 'Ery',
        'Geneticin' => 'Gen',
        'Gentamycin' => 'Gta',
        'Hygromycin' => 'Hgr',
        'Kanamycin' => 'Kan',
        'Kasugamycin' => 'Kas',
        'Nalidixic acid' => 'Nal',
        'Neomycin' => 'Neo',
        'Penicillin' => 'Pen',
        'Puromycin' => 'Pur',
        'Rifampicin' => 'Rif',
        'Spectinomycin' => 'Spe',
        'Streptomycin' => 'Str',
        'Tetracycline' => 'Tet',
        'Triclosan' => 'Tri',
        'Trimethoprim' => 'Tmp',
        'Zeocin' => 'Zeo',
    );

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
                        'label'     => 'Name'))
                ->add('type', ChoiceType::class, array(
                        'label' => 'Type',
                        'required'  => true,
                        'choices' => self::$types
                        ))
                ->add('size', NumberType::class, array(
                        'label'     => 'Size',
                        'attr'      => array('class' => 'input-small'),
                        'widget_addon_append' => array(
                                'text' => 'kb',
                        )))
                ->add('resistances', ChoiceType::class, array(
                        'label' => 'Resistances',
                        'required'  => true,
                        'multiple' => true,
                        'choices' => self::$antibiotics))
                ->add('notes', TextareaType::class, array(
                        'label' => 'Notes',
                        'required' => false))
                ->add('vendor', TextType::class, array(
                        'label' => 'Vendor',
                        'required' => false))
                ->add('infoURL', UrlType::class, array(
                        'label' => 'Info URL',
                        'required' => false,
                        'attr' => array('placeholder' => 'Paste address here')))
                ->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    array($this, 'onPreSetData'))
                ->addEventListener(
                    FormEvents::PRE_SUBMIT,
                    array($this, 'onPreSubmit'));
    }

    public function onPreSetData(FormEvent $event)
    {
        /** @var Construct $construct */
        $construct = $event->getData();
        $form = $event->getForm();

        if (null === $construct->getMethod()) {
            $form->add('cloningMethod', ChoiceType::class, array(
                        'label' => 'Cloning method',
                        'placeholder' => 'Select cloning method',
                        'required'  => true,
                        'mapped' => false,
                        'choices' => $this->getMethodsJSON()
            ));
        } else {
            $formType = $this->getFormTypeForMethod(get_class($construct->getMethod()));
            $form->add('method', $formType, array(
                'horizontal_label_offset_class' => null,
                'horizontal_input_wrapper_class' => 'construct_method_placeholder',
                'label_render'      => false,
                'widget_form_group' => false
            ));
        }
    }

    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (isset($data['cloningMethod']) && (! empty($data['cloningMethod']))) {
            $json = json_decode($data['cloningMethod'], true);
            $form->add('method', $json['form'], array(
                'horizontal_label_offset_class' => null,
                'horizontal_input_wrapper_class' => 'construct_method_placeholder',
                'label_render'      => false,
                'widget_form_group' => false
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Construct::class,
        ));
    }

    /**
     * @return array
     */
    private function getMethodsJSON()
    {
        $result = array();
        foreach (self::$methods as $name => $values) {
            $result[$name] = json_encode($values);
        }

        return $result;
    }

    /**
     * @var    string $method
     * @return string | null
     */
    private function getFormTypeForMethod($method)
    {
        foreach (self::$methods as $name => $values) {
            if ($values['entity'] == $method) {

                return $values['form'];
            }
        }

        return null;
    }
}
