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

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @Gedmo\Tree(type="nested")
 * @Gedmo\SoftDeleteable(timeAware=false)
 * @ORM\Entity(repositoryClass="ElementRepository")
 * @UniqueEntity(fields={"parent", "name"})
 */
class Element
{
    use SoftDeleteableEntity;

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
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $code;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $folder;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $included;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $locked;

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
     * @ORM\ManyToOne(targetEntity="Element", inversedBy="children")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     * @var Element
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Element", mappedBy="parent")
     * @var Collection
     */
    private $children;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @var Organization
     */
    private $organization;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="Reference", mappedBy="source")
     * @var Collection
     */
    private $references;

    /**
     * @ORM\ManyToMany(targetEntity="Element")
     * @ORM\JoinTable(name="label")
     * @ORM\OrderBy({"left" = "ASC"})
     * @var Collection
     */
    private $labels;

    /**
     * @ORM\ManyToOne(targetEntity="Element")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     * @var Element
     */
    private $managedBy;

    /**
     * @ORM\OneToOne(targetEntity="Profile", inversedBy="element")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     * @var Profile
     */
    private $profile;

    /**
     * @ORM\OneToMany(targetEntity="Actor", mappedBy="source")
     * @var Collection
     */
    private $actors;

    /**
     * @ORM\OneToMany(targetEntity="Role", mappedBy="element")
     * @var Collection
     */
    private $roles;

    /**
     * @ORM\OneToOne(targetEntity="Element")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     * @var Element
     */
    private $linkedTo;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->references = new \Doctrine\Common\Collections\ArrayCollection();
        $this->labels = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->actors = new \Doctrine\Common\Collections\ArrayCollection();

        $this->folder = false;
        $this->included = false;
        $this->locked = false;
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
     * @return Element
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
     * Set code
     *
     * @param string $code
     *
     * @return Element
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set folder
     *
     * @param boolean $folder
     *
     * @return Element
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Is folder
     *
     * @return boolean
     */
    public function isFolder()
    {
        return $this->folder;
    }

    /**
     * Set included
     *
     * @param boolean $included
     *
     * @return Element
     */
    public function setIncluded($included)
    {
        $this->included = $included;

        return $this;
    }

    /**
     * Is included
     *
     * @return boolean
     */
    public function isIncluded()
    {
        return $this->included;
    }

    /**
     * Is locked
     *
     * @return mixed
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * Set locked
     *
     * @param mixed $locked
     * @return Element
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
        return $this;
    }

    /**
     * Set left
     *
     * @param integer $left
     *
     * @return Element
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
     * @return Element
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
     * @return Element
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
     * @param Element $parent
     *
     * @return Element
     */
    public function setParent(Element $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Element
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add child
     *
     * @param Element $child
     *
     * @return Element
     */
    public function addChild(Element $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param Element $child
     */
    public function removeChild(Element $child)
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
     * @return Element
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
     * @return Element
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
     * Add reference
     *
     * @param Reference $reference
     *
     * @return Element
     */
    public function addReference(Reference $reference)
    {
        $this->references[] = $reference;

        return $this;
    }

    /**
     * Remove reference
     *
     * @param Reference $reference
     */
    public function removeReference(Reference $reference)
    {
        $this->references->removeElement($reference);
    }

    /**
     * Get references
     *
     * @return Collection
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Add label
     *
     * @param Element $label
     *
     * @return Element
     */
    public function addLabel(Element $label)
    {
        if (!$this->labels->contains($label)) {
            $this->labels[] = $label;
        }

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
     * Set managedBy
     *
     * @param Element $managedBy
     *
     * @return Element
     */
    public function setManagedBy(Element $managedBy = null)
    {
        $this->managedBy = $managedBy;

        return $this;
    }

    /**
     * Get managed element
     *
     * @return Element
     */
    public function getManagedBy()
    {
        return $this->managedBy;
    }

    /**
     * Set profile
     *
     * @param Profile $profile
     *
     * @return Element
     */
    public function setProfile(Profile $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Get actors
     *
     * @return Collection
     */
    public function getActors()
    {
        return $this->actors;
    }

    /**
     * Add actor
     *
     * @param Actor $actor
     *
     * @return Element
     */
    public function addActor(Actor $actor)
    {
        $this->actors[] = $actor;

        return $this;
    }

    /**
     * Remove actor
     *
     * @param User $actor
     *
     * @return Element
     */
    public function removeActor(User $actor)
    {
        $this->actors->removeElement($actor);

        return $this;
    }

    /**
     * Add role
     *
     * @param Role $role
     *
     * @return Element
     */
    public function addRole(Role $role)
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * Remove user
     *
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }
    }

    /**
     * Get roles
     *
     * @return Collection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Get full profile name
     */
    public function getFullProfileName()
    {
        $element = $this;
        $name = '';

        // mostrar nombre del elemento si no es el raiz
        $first = $element->getProfile() === null;

        while ($element && $element->getProfile() === null) {
            if ($first || $element->isIncluded() || !$element->isFolder()) {
                $name = $element->getName().' '.$name;
                $first = false;
            }
            $element = $element->getParent();
        }

        if ($element && ($profile = $element->getProfile())) {
            return trim((string) $profile.' '.$name);
        }

        return '';
    }

    /**
     * Get path collection
     */
    private function getPathCollection($method)
    {
        $result = new ArrayCollection();

        $element = $this;

        while ($element) {
            $items = $element->$method();

            foreach ($items as $collection) {
                $result->add($collection);
            }

            $element = $element->getParent();
        }

        return $result;
    }

    /**
     * Get path references
     */
    public function getPathReferences()
    {
        return $this->getPathCollection('getReferences');
    }

    /**
     * Get path actors
     */
    public function getPathActors()
    {
        return $this->getPathCollection('getActors');
    }

    /**
     * Get linkedTo
     *
     * @return Element
     */
    public function getLinkedTo()
    {
        return $this->linkedTo;
    }

    /**
     * Set linkedTo
     *
     * @param Element $linkedTo
     *
     * @return Element
     */
    public function setLinkedTo($linkedTo)
    {
        $this->linkedTo = $linkedTo;
        return $this;
    }
}
