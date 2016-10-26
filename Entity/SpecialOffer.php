<?php

namespace Fgms\SpecialOffersBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Fgms\SpecialOffersBundle\Repository\SpecialOfferRepository")
 * @ORM\Table(name="special_offer")
 */
class SpecialOffer
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $saleTitle;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime")
     */
    private $end;

    /**
     * @ORM\Column(type="text")
     */
    private $slideshow = '[]';

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $tag;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $saleSummary;

    /**
     * @ORM\Column(type="text")
     */
    private $variantIds = '[]';

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    private $discountCents;
    
    /**
     * @ORM\Column(type="float",nullable=true)
     */
    private $discountPercent;

    /**
     * @ORM\Column(type="string",length=7)
     */
    private $status;

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
     * Set saleTitle
     *
     * @param string $saleTitle
     *
     * @return SpecialOffer
     */
    public function setSaleTitle($saleTitle)
    {
        $this->saleTitle = $saleTitle;

        return $this;
    }

    /**
     * Get saleTitle
     *
     * @return string
     */
    public function getSaleTitle()
    {
        return $this->saleTitle;
    }

    /**
     * Set start
     *
     * @param \DateTime $start
     *
     * @return SpecialOffer
     */
    public function setStart($start)
    {
        $this->start = \Fgms\SpecialOffersBundle\Utility\DateTime::toDoctrine($start);

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start = \Fgms\SpecialOffersBundle\Utility\DateTime::fromDoctrine($this->start);
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     *
     * @return SpecialOffer
     */
    public function setEnd($end)
    {
        $this->end = \Fgms\SpecialOffersBundle\Utility\DateTime::toDoctrine($end);

        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end = \Fgms\SpecialOffersBundle\Utility\DateTime::fromDoctrine($this->end);
    }

    /**
     * Set slideshow
     *
     * @param array $slideshow
     *
     * @return SpecialOffer
     */
    public function setSlideshow(array $slideshow)
    {
        $this->slideshow = \Fgms\SpecialOffersBundle\Utility\Json::encode($slideshow);

        return $this;
    }

    /**
     * Get slideshow
     *
     * @return array
     */
    public function getSlideshow()
    {
        $retr = \Fgms\SpecialOffersBundle\Utility\Json::decode($this->slideshow);
        if (!is_array($retr)) throw new \LogicException('slideshow is not JSON array');
        return $retr;
    }

    /**
     * Set tag
     *
     * @param string $tag
     *
     * @return SpecialOffer
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set saleSummary
     *
     * @param string $saleSummary
     *
     * @return SpecialOffer
     */
    public function setSaleSummary($saleSummary)
    {
        $this->saleSummary = $saleSummary;

        return $this;
    }

    /**
     * Get saleSummary
     *
     * @return string
     */
    public function getSaleSummary()
    {
        return $this->saleSummary;
    }

    /**
     * Set discountCents
     *
     * @param integer $discountCents
     *
     * @return SpecialOffer
     */
    public function setDiscountCents($discountCents)
    {
        $this->discountCents = $discountCents;

        return $this;
    }

    /**
     * Get discountCents
     *
     * @return integer
     */
    public function getDiscountCents()
    {
        return $this->discountCents;
    }

    /**
     * Set discountPercent
     *
     * @param float $discountPercent
     *
     * @return SpecialOffer
     */
    public function setDiscountPercent($discountPercent)
    {
        $this->discountPercent = $discountPercent;

        return $this;
    }

    /**
     * Get discountPercent
     *
     * @return float
     */
    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }

    /**
     * Set variantIds
     *
     * @param array $variantIds
     *
     * @return SpecialOffer
     */
    public function setVariantIds(array $variantIds)
    {
        $this->variantIds = \Fgms\SpecialOffersBundle\Utility\Json::encode($variantIds);

        return $this;
    }

    /**
     * Get variantIds
     *
     * @return array
     */
    public function getVariantIds()
    {
        $retr = \Fgms\SpecialOffersBundle\Utility\Json::decode($this->variantIds);
        if (!is_array($retr)) throw new \LogicException('variantIds is not JSON array');
        return $retr;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return SpecialOffer
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
