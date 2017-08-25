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

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Role
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Element", inversedBy="roles")
     * @ORM\JoinColumn(nullable=false)
     * @var Element
     */
    private $element;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="assignedRoles")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    private $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Profile")
     * @ORM\JoinColumn(nullable=false)
     * @var Profile
     */
    private $profile;

    /**
     * Convert role to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getUser().' ('.$this->getProfile()->getName($this->getUser()).')';
    }

    /**
     * Convert role to profile code
     *
     * @return string
     */
    public function getProfileCode()
    {
        return 'profile.'.$this->getProfile()->getCode().'.'.($this->getUser()->getGender());
    }

    /**
     * Convert role to profile code
     *
     * @return string
     */
    public function getProfileCodeNeutral()
    {
        return 'profile.'.$this->getProfile()->getCode().'.'.(User::GENDER_NEUTRAL);
    }

    /**
     * Set role
     *
     * @param Profile $profile
     *
     * @return Role
     */
    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get role
     *
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set element
     *
     * @param Element $element
     *
     * @return Role
     */
    public function setElement(Element $element)
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
     * Set user
     *
     * @param User $user
     *
     * @return Role
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
