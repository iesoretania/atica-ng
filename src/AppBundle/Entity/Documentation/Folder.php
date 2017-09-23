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

use AppBundle\Entity\Organization;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="FolderRepository")
 * @ORM\Table(name="documentation_folder", indexes={@ORM\Index(columns={"lft"}), @ORM\Index(columns={"rght"})}))
 */
class Folder
{
    const VISIBILITY_NO_RESTRICTION = 0;
    const VISIBILITY_OWN_USER = 1;
    const VISIBILITY_OWN_PROFILE = 2;

    const GROUP_BY_NONE = 0;
    const GROUP_BY_USER = 1;
    const GROUP_BY_PROFILE = 2;

    const TYPE_NORMAL = 0;
    const TYPE_WORKFLOW = 1;
    const TYPE_TASKS = 2;

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
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     * @var int
     */
    private $left;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     * @var int
     */
    private $level;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rght", type="integer")
     * @var int
     */
    private $right;

    /**
     * @Gedmo\TreeParent()
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="children")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     * @var Folder
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Folder", mappedBy="parent")
     * @var Collection
     */
    private $children;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Organization")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @var Organization
     */
    private $organization;

    /**
     * @ORM\OneToMany(targetEntity="FolderPermission", mappedBy="folder")
     * @var Collection
     */
    private $permissions;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $versionShown;

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
     * @ORM\OneToMany(targetEntity="Entry", mappedBy="folder")
     * @var Collection
     */
    private $entries;

    /**
     * @ORM\OneToMany(targetEntity="Task", mappedBy="folder")
     * @var Collection
     */
    private $tasks;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $visibility;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $groupBy;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $autoArchive;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->entries = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();

        $this->versionShown = true;
        $this->public = false;
        $this->autoArchive = false;
        $this->type = $this::TYPE_NORMAL;
        $this->visibility = $this::VISIBILITY_NO_RESTRICTION;
        $this->groupBy = $this::GROUP_BY_NONE;
    }

    /**
     * Converts entity to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Get element path
     *
     * @return string
     */
    public function getPath()
    {
        $path = '';
        $item = $this;
        $first = true;

        while ($item) {
            $path = $item->getName().($first ? '' : '/').$path;
            $first = false;
            $item = $item->getParent();
        }

        return $path;
    }

    /**
     * Get element path array from specified root
     *
     * @param Folder|null $root
     *
     * @return Folder[]
     */
    public function getPathArray(Folder $root = null)
    {
        $path = [];
        $item = $this;

        while ($item && $item !== $root) {
            array_unshift($path, $item);
            $item = $item->getParent();
        }
        return $path;
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
     * @return Folder
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
     * Set left
     *
     * @param integer $left
     *
     * @return Folder
     */
    public function setLeft($left)
    {
        $this->left = $left;

        return $this;
    }

    /**
     * Get left
     *
     * @return integer
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * Set level
     *
     * @param integer $level
     *
     * @return Folder
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set right
     *
     * @param integer $right
     *
     * @return Folder
     */
    public function setRight($right)
    {
        $this->right = $right;

        return $this;
    }

    /**
     * Get right
     *
     * @return integer
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * Set parent
     *
     * @param Folder $parent
     *
     * @return Folder
     */
    public function setParent(Folder $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Folder
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add child
     *
     * @param Folder $child
     *
     * @return Folder
     */
    public function addChild(Folder $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param Folder $child
     */
    public function removeChild(Folder $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Folder
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
     * Set organization
     *
     * @param Organization $organization
     *
     * @return Folder
     */
    public function setOrganization(Organization $organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Add permission
     *
     * @param FolderPermission $permission
     *
     * @return Folder
     */
    public function addPermission(FolderPermission $permission)
    {
        $this->permissions[] = $permission;

        return $this;
    }

    /**
     * Remove permission
     *
     * @param FolderPermission $permission
     */
    public function removePermission(FolderPermission $permission)
    {
        $this->permissions->removeElement($permission);
    }

    /**
     * Get permissions
     *
     * @return Collection
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Set versionShown
     *
     * @param boolean $versionShown
     *
     * @return Folder
     */
    public function setVersionShown($versionShown)
    {
        $this->versionShown = $versionShown;

        return $this;
    }

    /**
     * Get versionShown
     *
     * @return boolean
     */
    public function isVersionShown()
    {
        return $this->versionShown;
    }

    /**
     * Set public
     *
     * @param boolean $public
     *
     * @return Folder
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return boolean
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * Set publicToken
     *
     * @param string $publicToken
     *
     * @return Folder
     */
    public function setPublicToken($publicToken)
    {
        $this->publicToken = $publicToken;

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
     * Add entry
     *
     * @param Entry $entry
     *
     * @return Folder
     */
    public function addEntry(Entry $entry)
    {
        $this->entries[] = $entry;

        return $this;
    }

    /**
     * Remove entry
     *
     * @param Entry $entry
     */
    public function removeEntry(Entry $entry)
    {
        $this->entries->removeElement($entry);
    }

    /**
     * Get entries
     *
     * @return Collection
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Set visibility
     *
     * @param integer $visibility
     *
     * @return Folder
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Get visibility
     *
     * @return integer
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set groupBy
     *
     * @param integer $groupBy
     *
     * @return Folder
     */
    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    /**
     * Get groupBy
     *
     * @return integer
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Folder
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add task
     *
     * @param Task $task
     *
     * @return Folder
     */
    public function addTask(Task $task)
    {
        $this->tasks[] = $task;

        return $this;
    }

    /**
     * Remove task
     *
     * @param Task $task
     */
    public function removeTask(Task $task)
    {
        $this->tasks->removeElement($task);
    }

    /**
     * Get tasks
     *
     * @return Collection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @return bool
     */
    public function isAutoArchive()
    {
        return $this->autoArchive;
    }

    /**
     * @param bool $autoArchive
     *
     * @return Folder
     */
    public function setAutoArchive($autoArchive)
    {
        $this->autoArchive = $autoArchive;
        return $this;
    }

}
