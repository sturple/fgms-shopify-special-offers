<?php

namespace Fgms\SpecialOffersBundle\Strategy;

/**
 * A SpecialOfferStrategy which actually applies and
 * reverts changes to Shopify.
 */
class SpecialOfferStrategy implements SpecialOfferStrategyInterface
{
    private $shopify;

    public function __construct(\Fgms\SpecialOffersBundle\Shopify\ClientInterface $shopify)
    {
        $this->shopify = $shopify;
    }

    private function getVariant($id)
    {
        $result = $this->shopify->call(
            'GET',
            sprintf('/admin/variants/%d',$id),
            ['fields' => 'id,product_id,compare_at_price,price']
        );
        return $result->getObject('variant');
    }

    private function getProduct($id)
    {
        $result = $this->shopify->call(
            'GET',
            sprintf('/admin/products/%d',$id),
            ['fields' => 'id,tags']
        );
        return $result->getObject('product');
    }

    private function getVariants(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer)
    {
        foreach ($offer->getVariantIds() as $id) {
            $v = $this->getVariant($id);
            yield (object)[
                'variant' => $v,
                'product' => $this->getProduct($v->getInteger('product_id'))
            ];
        }
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

    private function tagsToArray($tags)
    {
        if ($tags === '') return [];
        return preg_split('/,\s*/u',$tags);
    }

    private function arrayToTags(array $tags)
    {
        $retr = '';
        foreach ($tags as $tag) {
            if ($retr !== '') $retr .= ', ';
            $retr .= $tag;
        }
        return $retr;
    }

    private function tagsToMap(array $tags)
    {
        $retr = [];
        foreach ($tags as $tag) $retr[$tag] = true;
        return $retr;
    }

    private function mapToTags(array $map)
    {
        $retr = [];
        foreach ($map as $tag => $active) if ($active) $retr[] = $tag;
        return $retr;
    }

    private function addTags(array $initial, array $add)
    {
        $map = $this->tagsToMap($initial);
        foreach ($add as $tag) $map[$tag] = true;
        return $this->mapToTags($map);
    }

    private function removeTags(array $initial, array $remove)
    {
        $map = $this->tagsToMap($initial);
        foreach ($remove as $tag) $map[$tag] = false;
        return $this->mapToTags($map);
    }

    private function applyChanges(array $objs)
    {
        $retr = [];
        foreach ($objs as $obj) {
            $change = $obj->change;
            $retr[] = $change;
            $vid = $change->getVariantId();
            $pid = $obj->product->getInteger('id');
            $revert = $change->getType() === 'revert';
            $compare_at = $revert ? null : $this->toPrice($change->getBeforeCents());
            $variant = [
                'id' => $vid,
                'price' => $this->toPrice($change->getAfterCents()),
                'compare_at_price' => $compare_at
            ];
            $offer = $change->getSpecialOffer();
            $meta_key = sprintf('offer_%d',$offer->getId());
            //  Add metafield on apply
            if (!$revert) $variant['metafields'] = [
                [
                    'key' => $meta_key,
                    'namespace' => 'fgms_special_offers',
                    'value_type' => 'string',
                    'value' => 'Hello world!'
                ]
            ];
            $this->shopify->call('PUT',sprintf('/admin/variants/%d',$vid),[
                'variant' => $variant
            ]);
            $this->shopify->call('PUT',sprintf('/admin/products/%d',$pid),[
                'product' => [
                    'id' => $pid,
                    'tags' => $this->arrayToTags($change->getAfterTags())
                ]
            ]);
            //  Remove metafield on revert
            if ($revert) {
                $metafields = $this->shopify->call('GET',sprintf('/admin/variants/%d/metafields',$vid),[
                    'fields' => 'id',
                    'key' => $meta_key
                ])->getArray('metafields');
                foreach ($metafields as $metafield) {
                    $mid = $metafield->getInteger('id');
                    $this->shopify->call('DELETE',sprintf('/admin/variants/%d/metafields/%d',$vid,$mid));
                }
            }
        }
        return $retr;
    }

    public function apply(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer)
    {
        $dc = $offer->getDiscountCents();
        $dp = $offer->getDiscountPercent();
        if (is_null($dc) === is_null($dp)) throw new \LogicException(
            sprintf(
                'SpecialOffer %d has nonsensical discount (either both %% and $ or neither)',
                $offer->getId()
            )
        );
        //  Generate list of PriceChange entities
        $changes = [];
        foreach ($this->getVariants($offer) as $obj) {
            $v = $obj->variant;
            //  Price change
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
            if (is_null($dc)) $pc->setAfterCents(intval($price * (1.0 - ($dp / 100.0))));
            else $pc->setAfterCents($price - $dc);
            if ($pc->getAfterCents() < 0) throw new \LogicException(
                sprintf(
                    'SpecialOffer %d caused item %d to have price <$0.00',
                    $offer->getId(),
                    $vid
                )
            );
            //  Tags change
            $product = $obj->product;
            $tags = $this->tagsToArray($product->getString('tags'));
            $pc->setBeforeTags($tags)
                ->setAfterTags($this->addTags($tags,$offer->getTags()));
            $changes[] = (object)[
                'change' => $pc,
                'variant' => $v,
                'product' => $product
            ];
        }
        //  Apply price changes to Shopify
        return $this->applyChanges($changes);
    }

    public function revert(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer)
    {
        //  Generate list of PriceChange entities
        $changes = [];
        foreach ($this->getVariants($offer) as $obj) {
            $v = $obj->variant;
            //  Price change
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
            //  Tags change
            $product = $obj->product;
            $tags = $this->tagsToArray($product->getString('tags'));
            $pc->setBeforeTags($tags)
                ->setAfterTags($this->removeTags($tags,$offer->getTags()));
            $changes[] = (object)[
                'change' => $pc,
                'variant' => $v,
                'product' => $product
            ];
        }
        //  Apply price changes to Spotify
        return $this->applyChanges($changes);
    }
}
