<?php

namespace Fgms\SpecialOffersBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Fgms\SpecialOffersBundle\Repository\PriceChangeRepository")
 * @ORM\Table(name="price_change")
 */
class PriceChange
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="SpecialOffer",inversedBy="priceChanges")
     */
    private $specialOffer;

    /**
     * @ORM\Column(type="string",length=6)
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $beforeCents;

    /**
     * @ORM\Column(type="integer")
     */
    private $afterCents;

    /**
     * @ORM\Column(type="bigint")
     */
    private $variantId;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return PriceChange
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set beforeCents
     *
     * @param integer $beforeCents
     *
     * @return PriceChange
     */
    public function setBeforeCents($beforeCents)
    {
        $this->beforeCents = $beforeCents;

        return $this;
    }

    /**
     * Get beforeCents
     *
     * @return integer
     */
    public function getBeforeCents()
    {
        return $this->beforeCents;
    }

    /**
     * Set afterCents
     *
     * @param integer $afterCents
     *
     * @return PriceChange
     */
    public function setAfterCents($afterCents)
    {
        $this->afterCents = $afterCents;

        return $this;
    }

    /**
     * Get afterCents
     *
     * @return integer
     */
    public function getAfterCents()
    {
        return $this->afterCents;
    }

    /**
     * Set specialOffer
     *
     * @param \Fgms\SpecialOffersBundle\Entity\SpecialOffer $specialOffer
     *
     * @return PriceChange
     */
    public function setSpecialOffer(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $specialOffer = null)
    {
        $this->specialOffer = $specialOffer;

        return $this;
    }

    /**
     * Get specialOffer
     *
     * @return \Fgms\SpecialOffersBundle\Entity\SpecialOffer
     */
    public function getSpecialOffer()
    {
        return $this->specialOffer;
    }

    /**
     * Set variantId
     *
     * @param integer $variantId
     *
     * @return PriceChange
     */
    public function setVariantId($variantId)
    {
        $this->variantId = $variantId;

        return $this;
    }

    /**
     * Get variantId
     *
     * @return integer
     */
    public function getVariantId()
    {
        if (PHP_INT_SIZE < 8) throw new \LogicException('PHP_INT_SIZE must be at least 8');
        return intval($this->variantId);
    }
}
