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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 */
class User
{
    const GENDER_NEUTRAL = 0;
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     * @var string
     */
    private $userName;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $firstName;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $lastName;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $active;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     * @Assert\Email
     * @var string
     */
    private $emailAddress;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $gender;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $lastAccess;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $blockedUntil;

    /**
     * @ORM\OneToMany(targetEntity="Membership", mappedBy="user")
     * @var Collection
     */
    private $memberships;
}