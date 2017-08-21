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
class Reference
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Element", inversedBy="references")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @var Element
     */
    private $source;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Element")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @var Element
     */
    private $target;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $multiple;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $mandatory;

    /**
     * Convert reference to string
     *
     * @return string
     */
    public function __toString()
    {
        $data = (string) $this->getTarget();

        if ($this->isMultiple() || $this->isMandatory()) {
            $data .= ' (';
            $data .= $this->isMandatory() ? '1..' : '0..';

            if ($this->isMultiple()) {
                $data .= '*';
            }

            $data .= ')';
        }

        return $data;
    }

    /**
     * Set multiple
     *
     * @param boolean $multiple
     *
     * @return Reference
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * Get multiple
     *
     * @return boolean
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * Set mandatory
     *
     * @param boolean $mandatory
     *
     * @return Reference
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    /**
     * Get mandatory
     *
     * @return boolean
     */
    public function isMandatory()
    {
        return $this->mandatory;
    }

    /**
     * Set source
     *
     * @param Element $source
     *
     * @return Reference
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
     * Set target
     *
     * @param Element $target
     *
     * @return Reference
     */
    public function setTarget(Element $target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return Element
     */
    public function getTarget()
    {
        return $this->target;
    }
}
