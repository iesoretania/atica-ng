<?php
/*
  ÁTICA - Aplicación web para la gestión documental de centros educativos

  Copyright (C) 2015-2017: Luis Ramón López López

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Affero General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Affero General Public License for more details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see [http://www.gnu.org/licenses/].
*/

namespace AppBundle\Entity\Documentation;

use AppBundle\Entity\Traits\UserBlameableTrait;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documentation_version")
 */
class Version
{
    const STATUS_DRAFT = 0;
    const STATUS_REVIEWED = 1;
    const STATUS_APPROVED = 2;
    const STATUS_DEPRECATED = 3;
    const STATUS_LOCKED = 4;
    const STATUS_DISCARDED = 5;

    use TimestampableEntity;
    use UserBlameableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $state;

    /**
     * @Gedmo\Timestampable(on="change", field="state")
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $stateChangedAt;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $versionNr;

    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="versions")
     * @ORM\JoinColumn(nullable=false)
     * @var Entry
     */
    private $entry;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $file;

    public function __construct()
    {
        $this->state = $this::STATUS_DRAFT;
        $this->stateChangedAt = new \DateTime();
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
     * Set description
     *
     * @param string $description
     *
     * @return Version
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set state
     *
     * @param integer $state
     *
     * @return Version
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set stateChangedAt
     *
     * @param \DateTime $stateChangedAt
     *
     * @return Version
     */
    public function setStateChangedAt($stateChangedAt)
    {
        $this->stateChangedAt = $stateChangedAt;

        return $this;
    }

    /**
     * Get stateChangedAt
     *
     * @return \DateTime
     */
    public function getStateChangedAt()
    {
        return $this->stateChangedAt;
    }

    /**
     * Set versionNr
     *
     * @param integer $versionNr
     *
     * @return Version
     */
    public function setVersionNr($versionNr)
    {
        $this->versionNr = $versionNr;

        return $this;
    }

    /**
     * Get versionNr
     *
     * @return integer
     */
    public function getVersionNr()
    {
        return $this->versionNr;
    }

    /**
     * Set entry
     *
     * @param Entry $entry
     *
     * @return Version
     */
    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * Get entry
     *
     * @return Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     *
     * @return Version
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }
}
