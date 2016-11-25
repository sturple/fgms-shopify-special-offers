<?php

namespace Fgms\SpecialOffersBundle\Form\Type;

class LinkedDateTimeType extends \Symfony\Component\Form\Extension\Core\Type\DateTimeType
{
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefined('linked')
            ->setAllowedTypes('linked','string')
            ->setRequired('linked',true);
        $resolver->setDefined('first')
            ->setAllowedTypes('first','bool')
            ->setRequired('first',true);
    }

    public function buildView(\Symfony\Component\Form\FormView $view, \Symfony\Component\Form\FormInterface $form, array $options)
    {
        parent::buildView($view,$form,$options);
        $view->vars['linked'] = $options['linked'];
        $view->vars['first'] = $options['first'];
    }
}
