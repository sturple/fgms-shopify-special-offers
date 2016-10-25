<?php

namespace Fgms\SpecialOffersBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Fgms\SpecialOffersBundle\Repository\RunRepository")
 * @ORM\Table(name="run")
 */
class Run
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime",name="`when`")
     */
    private $when;

    /**
     * @ORM\Column(type="integer")
     */
    private $started;

    /**
     * @ORM\Column(type="integer")
     */
    private $ended;

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
     * Set when
     *
     * @param \DateTime $when
     *
     * @return Run
     */
    public function setWhen($when)
    {
        $this->when = $when;

        return $this;
    }

    /**
     * Get when
     *
     * @return \DateTime
     */
    public function getWhen()
    {
        return $this->when;
    }

    /**
     * Set started
     *
     * @param integer $started
     *
     * @return Run
     */
    public function setStarted($started)
    {
        $this->started = $started;

        return $this;
    }

    /**
     * Get started
     *
     * @return integer
     */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * Set ended
     *
     * @param integer $ended
     *
     * @return Run
     */
    public function setEnded($ended)
    {
        $this->ended = $ended;

        return $this;
    }

    /**
     * Get ended
     *
     * @return integer
     */
    public function getEnded()
    {
        return $this->ended;
    }
}
