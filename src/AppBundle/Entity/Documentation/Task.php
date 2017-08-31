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
use AppBundle\Entity\Periodicity;
use AppBundle\Entity\Traits\UserBlameableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documentation_task")
 */
class Task
{
    use TimestampableEntity;
    use UserBlameableTrait;

    const TASK_DELIVERY_BY_PROFILE = 0;
    const TASK_DELIVERY_BY_USER = 1;

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
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     * @var Folder
     */
    private $folder;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $toDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $fromDate;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $gracePeriod;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Periodicity")
     * @ORM\JoinColumn(nullable=true)
     * @var Periodicity
     */
    private $periodicity;

    /**
     * @ORM\OneToMany(targetEntity="TaskPermission", mappedBy="task")
     * @var Collection
     */
    private $permissions;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Element")
     * @ORM\JoinTable(name="documentation_task_label")
     * @var Collection
     */
    private $labels;

    /**
     * @ORM\ManyToMany(targetEntity="Entry")
     * @ORM\JoinTable(name="documentation_task_entries")
     * @var Collection
     */
    private $relatedEntries;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $documentNameTemplate;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $deliveryType;

    /**
     * Task constructor.
     */
    public function __construct()
    {
        $this->permissions = new ArrayCollection();
        $this->labels = new ArrayCollection();
        $this->relatedEntries = new ArrayCollection();

        $this->gracePeriod = 0;
        $this->deliveryType = self::TASK_DELIVERY_BY_PROFILE;
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
     * @return Task
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
     * @return Task
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
     * Set toDate
     *
     * @param \DateTime $toDate
     *
     * @return Task
     */
    public function setToDate($toDate)
    {
        $this->toDate = $toDate;

        return $this;
    }

    /**
     * Get toDate
     *
     * @return \DateTime
     */
    public function getToDate()
    {
        return $this->toDate;
    }

    /**
     * Set fromDate
     *
     * @param \DateTime $fromDate
     *
     * @return Task
     */
    public function setFromDate($fromDate)
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    /**
     * Get fromDate
     *
     * @return \DateTime
     */
    public function getFromDate()
    {
        return $this->fromDate;
    }

    /**
     * Set gracePeriod
     *
     * @param integer $gracePeriod
     *
     * @return Task
     */
    public function setGracePeriod($gracePeriod)
    {
        $this->gracePeriod = $gracePeriod;

        return $this;
    }

    /**
     * Get gracePeriod
     *
     * @return integer
     */
    public function getGracePeriod()
    {
        return $this->gracePeriod;
    }

    /**
     * Set documentNameTemplate
     *
     * @param string $documentNameTemplate
     *
     * @return Task
     */
    public function setDocumentNameTemplate($documentNameTemplate)
    {
        $this->documentNameTemplate = $documentNameTemplate;

        return $this;
    }

    /**
     * Get documentNameTemplate
     *
     * @return string
     */
    public function getDocumentNameTemplate()
    {
        return $this->documentNameTemplate;
    }

    /**
     * Set deliveryType
     *
     * @param integer $deliveryType
     *
     * @return Task
     */
    public function setDeliveryType($deliveryType)
    {
        $this->deliveryType = $deliveryType;

        return $this;
    }

    /**
     * Get deliveryType
     *
     * @return integer
     */
    public function getDeliveryType()
    {
        return $this->deliveryType;
    }

    /**
     * Set folder
     *
     * @param Folder $folder
     *
     * @return Task
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
     * Set periodicity
     *
     * @param Periodicity $periodicity
     *
     * @return Task
     */
    public function setPeriodicity(Periodicity $periodicity = null)
    {
        $this->periodicity = $periodicity;

        return $this;
    }

    /**
     * Get periodicity
     *
     * @return Periodicity
     */
    public function getPeriodicity()
    {
        return $this->periodicity;
    }

    /**
     * Add label
     *
     * @param Element $label
     *
     * @return Task
     */
    public function addLabel(Element $label)
    {
        $this->labels[] = $label;

        return $this;
    }

    /**
     * Remove label
     *
     * @param Element $label
     */
    public function removeLabel(Element $label)
    {
        $this->labels->removeElement($label);
    }

    /**
     * Get labels
     *
     * @return Collection
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Add relatedEntry
     *
     * @param Entry $relatedEntry
     *
     * @return Task
     */
    public function addRelatedEntry(Entry $relatedEntry)
    {
        $this->relatedEntries[] = $relatedEntry;

        return $this;
    }

    /**
     * Remove relatedEntry
     *
     * @param Entry $relatedEntry
     */
    public function removeRelatedEntry(Entry $relatedEntry)
    {
        $this->relatedEntries->removeElement($relatedEntry);
    }

    /**
     * Get relatedEntries
     *
     * @return Collection
     */
    public function getRelatedEntries()
    {
        return $this->relatedEntries;
    }

    /**
     * Add permission
     *
     * @param TaskPermission $permission
     *
     * @return Task
     */
    public function addPermission(TaskPermission $permission)
    {
        $this->permissions[] = $permission;

        return $this;
    }

    /**
     * Remove permission
     *
     * @param TaskPermission $permission
     */
    public function removePermission(TaskPermission $permission)
    {
        $this->permissions->removeElement($permission);
    }

    /**
     * Get permissions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
