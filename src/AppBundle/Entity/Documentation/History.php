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

use AppBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documentation_history")
 */
class History
{
    const LOG_CREATE = 0;
    const LOG_DRAFT_UPLOADED = 1;
    const LOG_REVIEW_REQUESTED = 2;
    const LOG_REVIEWED_OK = 3;
    const LOG_REVIEWED_REJECTED = 4;
    const LOG_APPROVE_REQUESTED = 5;
    const LOG_APPROVED_OK = 6;
    const LOG_APPROVED_REJECTED = 7;
    const LOG_CHANGE_REQUESTED = 8;
    const LOG_CHANGE_OK = 9;
    const LOG_CHANGE_REJECTED = 10;
    const LOG_DEPRECATED = 11;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    private $comment;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $event;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    private $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="history")
     * @ORM\JoinColumn(nullable=false)
     * @var Entry
     */
    private $entry;

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
     * Set comment
     *
     * @param string $comment
     *
     * @return History
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set event
     *
     * @param integer $event
     *
     * @return History
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return integer
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return History
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return History
     */
    public function setCreatedBy(User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set entry
     *
     * @param Entry $entry
     *
     * @return History
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
}
