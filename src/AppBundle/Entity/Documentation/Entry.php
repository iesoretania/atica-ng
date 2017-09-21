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

use AppBundle\Entity\Element;
use AppBundle\Entity\HistoricPeriod;
use AppBundle\Entity\Traits\UserBlameableTrait;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="documentation_entry")
 */
class Entry
{
    const STATUS_APPROVED = 2;
    const STATUS_DRAFT = 0;
    const STATUS_RETIRED = 6;

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
     * @ORM\Column(type="string")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $description;

    /**
     * @Gedmo\SortablePosition()
     * @ORM\Column(type="integer")
     * @var int
     */
    private $position;

    /**
     * @Gedmo\SortableGroup()
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="entries")
     * @ORM\JoinColumn(nullable=false)
     * @var Folder
     */
    private $folder;

    /**
     * @Gedmo\SortableGroup()
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\HistoricPeriod")
     * @ORM\JoinColumn(nullable=true)
     * @var HistoricPeriod
     */
    private $archivedPeriod;

    /**
     * @ORM\ManyToOne(targetEntity="Entry")
     * @ORM\JoinColumn(nullable=true)
     * @var Entry
     */
    private $link;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Element")
     * @ORM\JoinColumn(nullable=true)
     * @var Element
     */
    private $element;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $retiredAt;

    /**
     * @ORM\OneToMany(targetEntity="Version", mappedBy="entry")
     * @ORM\OrderBy({"versionNr": "DESC"})
     * @var Collection
     */
    private $versions;

    /**
     * @ORM\OneToMany(targetEntity="History", mappedBy="entry")
     * @ORM\OrderBy({"createdAt": "ASC"})
     * @var Collection
     */
    private $history;

    /**
     * @ORM\OneToOne(targetEntity="Version")
     * @var Version
     */
    private $currentVersion;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $public;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $publicToken;

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
     * Constructor
     */
    public function __construct()
    {
        $this->versions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->history = new \Doctrine\Common\Collections\ArrayCollection();
        $this->public = false;
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
     * Set name
     *
     * @param string $name
     *
     * @return Entry
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
     * Set description
     *
     * @param string $description
     *
     * @return Entry
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
     * Set position
     *
     * @param integer $position
     *
     * @return Entry
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set retiredAt
     *
     * @param \DateTime $retiredAt
     *
     * @return Entry
     */
    public function setRetiredAt($retiredAt)
    {
        $this->retiredAt = $retiredAt;

        return $this;
    }

    /**
     * Get retiredAt
     *
     * @return \DateTime
     */
    public function getRetiredAt()
    {
        return $this->retiredAt;
    }

    /**
     * Set folder
     *
     * @param Folder $folder
     *
     * @return Entry
     */
    public function setFolder(Folder $folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get folder
     *
     * @return Folder
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set link
     *
     * @param Entry $link
     *
     * @return Entry
     */
    public function setLink(Entry $link = null)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return Entry
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Add version
     *
     * @param Version $version
     *
     * @return Entry
     */
    public function addVersion(Version $version)
    {
        $this->versions[] = $version;

        return $this;
    }

    /**
     * Remove version
     *
     * @param Version $version
     */
    public function removeVersion(Version $version)
    {
        $this->versions->removeElement($version);
    }

    /**
     * Get versions
     *
     * @return Collection
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * Add history
     *
     * @param History $history
     *
     * @return Entry
     */
    public function addHistory(History $history)
    {
        $this->history[] = $history;

        return $this;
    }

    /**
     * Remove history
     *
     * @param History $history
     */
    public function removeHistory(History $history)
    {
        $this->history->removeElement($history);
    }

    /**
     * Get history
     *
     * @return Collection
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * Set element
     *
     * @param Element $element
     *
     * @return Entry
     */
    public function setElement(Element $element = null)
    {
        $this->element = $element;

        return $this;
    }

    /**
     * Get element
     *
     * @return Element
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Set archivedPeriod
     *
     * @param HistoricPeriod $archivedPeriod
     *
     * @return Entry
     */
    public function setArchivedPeriod(HistoricPeriod $archivedPeriod = null)
    {
        $this->archivedPeriod = $archivedPeriod;

        return $this;
    }

    /**
     * Get archivedPeriod
     *
     * @return HistoricPeriod
     */
    public function getArchivedPeriod()
    {
        return $this->archivedPeriod;
    }

    /**
     * @return Version
     */
    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    /**
     * @param Version $currentVersion
     * @return Entry
     */
    public function setCurrentVersion($currentVersion = null)
    {
        $this->currentVersion = $currentVersion;
        return $this;
    }

    /**
     * Get public
     *
     * @return bool
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * Set public
     *
     * @param bool $public
     *
     * @return Entry
     */
    public function setPublic($public)
    {
        $this->public = $public;
        return $this;
    }

    /**
     * Get publicToken
     *
     * @return string
     */
    public function getPublicToken()
    {
        return $this->publicToken;
    }

    /**
     * Set publicToken
     *
     * @param string $publicToken
     *
     * @return Entry
     */
    public function setPublicToken($publicToken)
    {
        $this->publicToken = $publicToken;
        return $this;
    }

    /**
     * Set state
     *
     * @param integer $state
     *
     * @return Entry
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
     * @return Entry
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
}
