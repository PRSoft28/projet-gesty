<?php

namespace WCS\CantineBundle\Entity;

/**
 * TacheBase
 */
class ActivityBase
{
    const STATUS_REGISTERED_BUT_ABSENT  = 0;
    const STATUS_NOT_REGISTERED         = 1;
    const STATUS_REGISTERED_AND_PRESENT = 2;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var integer
     */
    protected $status;

    /**
     * @var \WCS\CantineBundle\Entity\Eleve
     */
    protected $eleve;


    public function __construct()
    {
        $this->status = self::STATUS_REGISTERED_BUT_ABSENT;
    }

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
     * Set date
     *
     * @param \DateTimeInterface $date
     *
     * @return Tap
     */
    public function setDate(\DateTimeInterface $date)
    {
        $this->date = new \DateTime($date->format('Y-m-d H:i:s'));

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Tap
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set eleve
     *
     * @param \WCS\CantineBundle\Entity\Eleve $eleve
     *
     * @return Tap
     */
    public function setEleve(\WCS\CantineBundle\Entity\Eleve $eleve = null)
    {
        $this->eleve = $eleve;

        return $this;
    }

    /**
     * Get eleve
     *
     * @return \WCS\CantineBundle\Entity\Eleve
     */
    public function getEleve()
    {
        return $this->eleve;
    }
}
