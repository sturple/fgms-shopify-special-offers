<?php

namespace Fgms\SpecialOffersBundle\Form\Type;

class TagsType extends \Symfony\Component\Form\AbstractType implements \Symfony\Component\Form\DataTransformerInterface
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $fb, array $options)
    {
        $fb->addViewTransformer($this);
    }

    public function transform($data)
    {
        if (is_null($data)) return '';
        return implode(', ',$data);
    }

    public function reverseTransform($data)
    {
        if (!is_string($data)) return [];
        $data = trim($data);
        if ($data === '') return [];
        return preg_split('/,\\s*/u',$data);
    }

    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\TextType::class;
    }

    public function getBlockPrefix()
    {
        return 'fgms_special_offers_tags';
    }
}
