<?php

namespace Fgms\SpecialOffersBundle\Strategy;

/**
 * A SpecialOfferStrategy which actually applies and
 * reverts changes to Shopify.
 */
class SpecialOfferStrategy implements SpecialOfferStrategyInterface
{
    private $shopify;

    public function __construct(\Fgms\SpecialOffersBundle\Utility\ShopifyInterface $shopify)
    {
        $this->shopify = $shopify;
    }

    private function getVariant($id)
    {
        return $this->shopify->call('GET',sprintf('/admin/variants/%d.json',$id))->getObject('variant');
    }

    private function getVariants(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer)
    {
        $retr = [];
        foreach ($offer->getVariantIds() as $id) $retr[] = $this->getVariant($id);
        return $retr;
    }

    private function toCents($str)
    {
        if (preg_match('/^([1-9][0-9]*|0)\\.([0-9]{2})$/u',$str,$matches) !== 1) throw new \Fgms\SpecialOffersBundle\Exception\ShopifyException(
            'Shopify returned invalid price string: %s',
            $str
        );
        $dollars = intval($matches[1]);
        $cents = intval($matches[2]);
        $cents += $dollars * 100;
        return $cents;
    }

    private function toPrice($cents)
    {
        $dollars = intdiv($cents,100);
        $cents = $cents % 100;
        return sprintf(
            '%d.%02d',
            $dollars,
            $cents
        );
    }

    public function apply(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer)
    {
        //  TODO: Tags
        $vs = $this->getVariants($offer);
        $dc = $offer->getDiscountCents();
        $dp = $offer->getDiscountPercent();
        if (is_null($dc) === is_null($dp)) throw new \LogicException(
            sprintf(
                'SpecialOffer %d has nonsensical discount (either both %% and $ or neither)',
                $offer->getId()
            )
        );
        //  Generate list of PriceChange entities
        $retr = [];
        foreach ($vs as $v) {
            $compare_at = $v->getOptionalString('compare_at_price');
            $vid = $v->getInteger('id');
            if (!is_null($compare_at)) throw new \Fgms\SpecialOffersBundle\Exception\AlreadyOnSpecialOfferException(
                $vid,
                $offer
            );
            $price = $this->toCents($v->getString('price'));
            $pc = new \Fgms\SpecialOffersBundle\Entity\PriceChange();
            $pc->setType('apply')
                ->setSpecialOffer($offer)
                ->setVariantId($vid)
                ->setBeforeCents($price);
            if (is_null($dc)) $pc->setAfterCents(intval($price * $dp));
            else $pc->setAfterCents($price - $dc);
            if ($pc->getAfterCents() < 0) throw new \LogicException(
                sprintf(
                    'SpecialOffer %d caused item %d to have price <$0.00',
                    $offer->getId(),
                    $vid
                )
            );
            $retr[] = $pc;
        }
        //  Apply price changes to Shopify
        foreach ($retr as $change) {
            $vid = $change->getVariantId();
            $this->shopify->call('PUT',sprintf('/admin/variants/%d.json',$vid),[
                'variant' => [
                    'id' => $vid,
                    'compare_at_price' => $this->toPrice($change->getBeforeCents()),
                    'price' => $this->toPrice($change->getAfterCents())
                ]
            ]);
        }
        return $retr;
    }

    public function revert(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer)
    {
        //  TODO: Tags
        $vs = $this->getVariants($offer);
        //  Generate list of PriceChange entities
        $retr = [];
        foreach ($vs as $v) {
            $vid = $v->getInteger('id');
            $compare_at = $v->getOptionalString('compare_at_price');
            if (is_null($compare_at)) throw new \Fgms\SpecialOffersBundle\Exception\NotOnSpecialOfferException(
                $vid,
                $offer
            );
            $compare_at = $this->toCents($compare_at);
            $price = $this->toCents($v->getString('price'));
            $pc = new \Fgms\SpecialOffersBundle\Entity\PriceChange();
            $pc->setType('revert')
                ->setSpecialOffer($offer)
                ->setVariantId($vid)
                ->setBeforeCents($price)
                ->setAfterCents($compare_at);
            $retr[] = $pc;
        }
        //  Apply price changes to Spotify
        foreach ($retr as $change) {
            $vid = $change->getVariantId();
            $this->shopify->call('PUT',sprintf('/admin/variants/%d.json',$vid),[
                'variant' => [
                    'id' => $vid,
                    'compare_at_price' => null,
                    'price' => $this->toPrice($compare_at)
                ]
            ]);
        }
        return $retr;
    }
}
