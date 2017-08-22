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
class Actor
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Element", inversedBy="actors")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @var Element
     */
    private $source;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Profile")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @var Profile
     */
    private $profile;

    /**
     * Set source
     *
     * @param Element $source
     *
     * @return Actor
     */
    public function setSource(Element $source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return Element
     */
    public function getSource()
    {
        return $this->source;
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
     * Set role
     *
     * @param Profile $profile
     * @return Actor
     */
    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;
        return $this;
    }

}
