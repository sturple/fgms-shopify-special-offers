<?php

namespace Fgms\SpecialOffersBundle\Form\Type;

class DiscountType extends \Symfony\Component\Form\AbstractType implements \Symfony\Component\Form\DataTransformerInterface
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $fb, array $options)
    {
        $fb->addViewTransformer($this)
            ->add('type',\Symfony\Component\Form\Extension\Core\Type\TextType::class)
            ->add('value',\Symfony\Component\Form\Extension\Core\Type\TextType::class,['required' => true]);
    }

    private function raise($msg, $e = null)
    {
        throw new \Symfony\Component\Form\Exception\TransformationFailedException($msg,0,$e);
    }

    public function transform($data)
    {
        if (!is_array($data)) return [];
        $is_percent = isset($data['percent']);
        $is_cents = isset($data['cents']);
        if ($is_percent === $is_cents) $this->raise('Both percent and cents, or neither');
        if ($is_percent) {
            $percent = $data['percent'];
            if (!is_float($percent)) $this->raise('Percent not float');
            return [
                'type' => '%',
                'value' => (string)round($percent,1)
            ];
        }
        $cents = $data['cents'];
        if (!is_integer($cents)) $this->raise('Cents non-integer');
        return [
            'type' => '$',
            'value' => sprintf('%.2f',round(floatval($cents) / 100.0,2))
        ];
    }

    public function reverseTransform($data)
    {
        $type = $data['type'];
        $value = $data['value'];
        if ($type === '%') {
            try {
                $p = \Fgms\SpecialOffersBundle\Utility\Convert::toFloat($value);
            } catch (\Fgms\SpecialOffersBundle\Exception\ConvertException $e) {
                $this->raise($e->getMessage(),$e);
            }
            if ($p < 0) $this->raise('Percentage discount negative');
            return [
                'cents' => null,
                'percent' => $p
            ];
        }
        if ($type === '$') {
            try {
                $cs = \Fgms\SpecialOffersBundle\Utility\Convert::toCents($value);
            } catch (\Fgms\SpecialOffersBundle\Exception\ConvertException $e) {
                $this->raise($e->getMessage(),$e);
            }
            if ($cs < 0) $this->raise('Cents discount negative');
            return [
                'percent' => null,
                'cents' => $cs
            ];
        }
        $this->raise('Unrecognized type');
    }

    public function buildView(\Symfony\Component\Form\FormView $view, \Symfony\Component\Form\FormInterface $form, array $options)
    {
        $label = preg_replace('/\\{\\{amount\\}\\}/u','',$options['money_with_currency_format']);
        $view->vars['cents_label'] = $label;
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefined('money_with_currency_format')
            ->setAllowedTypes('money_with_currency_format','string')
            ->setRequired('money_with_currency_format',true);
    }

    public function getBlockPrefix()
    {
        return 'fgms_special_offers_discount';
    }
}
