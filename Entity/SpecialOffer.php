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
     * @ORM\Column(type="text")
     */
    private $tags = '[]';

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
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $applied;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $reverted;

    /**
     * @ORM\OneToMany(targetEntity="PriceChange",mappedBy="specialOffer")
     */
    private $priceChanges;

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
        return \Fgms\SpecialOffersBundle\Utility\Json::decodeObjectArray($this->slideshow);
    }

    /**
     * Set tags
     *
     * @param array $tags
     *
     * @return SpecialOffer
     */
    public function setTags(array $tags)
    {
        $this->tags = \Fgms\SpecialOffersBundle\Utility\Json::encode($tags);

        return $this;
    }

    /**
     * Get tags
     *
     * @return array
     */
    public function getTags()
    {
        return \Fgms\SpecialOffersBundle\Utility\Json::decodeStringArray($this->tags);
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
        return \Fgms\SpecialOffersBundle\Utility\Json::decodeIntegerArray($this->variantIds);
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

    /**
     * Set applied
     *
     * @param \DateTime $applied
     *
     * @return SpecialOffer
     */
    public function setApplied($applied)
    {
        $this->applied = \Fgms\SpecialOffersBundle\Utility\DateTime::toDoctrine($applied);

        return $this;
    }

    /**
     * Get applied
     *
     * @return \DateTime
     */
    public function getApplied()
    {
        return $this->applied = \Fgms\SpecialOffersBundle\Utility\DateTime::fromDoctrine($this->applied);
    }

    /**
     * Set reverted
     *
     * @param \DateTime $reverted
     *
     * @return SpecialOffer
     */
    public function setReverted($reverted)
    {
        $this->reverted = \Fgms\SpecialOffersBundle\Utility\DateTime::toDoctrine($reverted);

        return $this;
    }

    /**
     * Get reverted
     *
     * @return \DateTime
     */
    public function getReverted()
    {
        return $this->reverted = \Fgms\SpecialOffersBundle\Utility\DateTime::fromDoctrine($this->reverted);
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->priceChanges = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add priceChange
     *
     * @param \Fgms\SpecialOffersBundle\Entity\PriceChange $priceChange
     *
     * @return SpecialOffer
     */
    public function addPriceChange(\Fgms\SpecialOffersBundle\Entity\PriceChange $priceChange)
    {
        $this->priceChanges[] = $priceChange;

        return $this;
    }

    /**
     * Remove priceChange
     *
     * @param \Fgms\SpecialOffersBundle\Entity\PriceChange $priceChange
     */
    public function removePriceChange(\Fgms\SpecialOffersBundle\Entity\PriceChange $priceChange)
    {
        $this->priceChanges->removeElement($priceChange);
    }

    /**
     * Get priceChanges
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPriceChanges()
    {
        return $this->priceChanges;
    }
}
