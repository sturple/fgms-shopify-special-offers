<?php

namespace Fgms\SpecialOffersBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Fgms\SpecialOffersBundle\Repository\StoreRepository")
 * @ORM\Table(name="store")
 */
class Store
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string",length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string",length=255)
     */
    private $accessToken;

    /**
     * @ORM\Column(type="string",length=100)
     */
    private $status = 'active';

    /**
     * @ORM\OneToMany(targetEntity="SpecialOffer",mappedBy="store")
     */
    private $specialOffers;

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
     * Set name
     *
     * @param string $name
     *
     * @return Store
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set accessToken
     *
     * @param string $accessToken
     *
     * @return Store
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Store
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
     * Constructor
     */
    public function __construct()
    {
        $this->specialOffers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add specialOffer
     *
     * @param \Fgms\SpecialOffersBundle\Entity\SpecialOffer $specialOffer
     *
     * @return Store
     */
    public function addSpecialOffer(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $specialOffer)
    {
        $this->specialOffers[] = $specialOffer;

        return $this;
    }

    /**
     * Remove specialOffer
     *
     * @param \Fgms\SpecialOffersBundle\Entity\SpecialOffer $specialOffer
     */
    public function removeSpecialOffer(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $specialOffer)
    {
        $this->specialOffers->removeElement($specialOffer);
    }

    /**
     * Get specialOffers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSpecialOffers()
    {
        return $this->specialOffers;
    }
}
