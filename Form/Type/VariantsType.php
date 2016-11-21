<?php

namespace Fgms\SpecialOffersBundle\Form\Type;

class VariantsType extends \Symfony\Component\Form\AbstractType implements \Symfony\Component\Form\DataTransformerInterface
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $fb, array $options)
    {
        $fb->addViewTransformer($this);
    }

    public function transform($data)
    {
        if (is_null($data)) return '';
        return implode(',',$data);
    }

    public function reverseTransform($data)
    {
        if (!is_string($data)) return [];
        $data = trim($data);
        if ($data === '') return [];
        $arr = preg_split('/,\\s*/u',$data);
        return array_map(function ($str) {  return intval($str);    },$arr);
    }

    public function buildView(\Symfony\Component\Form\FormView $view, \Symfony\Component\Form\FormInterface $form, array $options)
    {
        $collator = new \Collator(null);
        $products = array_map(function (\Fgms\SpecialOffersBundle\Shopify\ObjectWrapper $obj) use ($collator) {
            $variants = array_map(function (\Fgms\SpecialOffersBundle\Shopify\ObjectWrapper $obj) {
                return (object)[
                    'id' => $obj->getInteger('id'),
                    'title' => $obj->getString('title')
                ];
            },iterator_to_array($obj->getArray('variants')));
            usort($variants,function ($a, $b) use ($collator) {
                return $collator->compare($a->title,$b->title);
            });
            return (object)[
                'id' => $obj->getInteger('id'),
                'title' => $obj->getString('title'),
                'variants' => $variants
            ];
        },$options['products']);
        usort($products,function ($a, $b) use ($collator) {
            return $collator->compare($a->title,$b->title);
        });
        $view->vars['products'] = $products;
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefined('products')
            ->setAllowedTypes('products','array')
            ->setAllowedValues('products',function (array $arr) {
                return array_reduce($arr,function ($carry, $item) {
                    if (!($item instanceof \Fgms\SpecialOffersBundle\Shopify\ObjectWrapper)) return false;
                    return $carry;
                },true);
            })
            ->setRequired('products',true);
    }

    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\TextType::class;
    }

    public function getBlockPrefix()
    {
        return 'fgms_special_offers_variants';
    }
}
