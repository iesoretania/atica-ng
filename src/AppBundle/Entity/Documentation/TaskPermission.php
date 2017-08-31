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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documentation_task_permission")
 */
class TaskPermission
{
    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Task", inversedBy="permissions")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @var Task
     */
    private $task;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Element")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @var Element
     */
    private $element;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    private $permission;

    /**
     * Set permission
     *
     * @param integer $permission
     *
     * @return TaskPermission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Get permission
     *
     * @return integer
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Set task
     *
     * @param Task $task
     *
     * @return TaskPermission
     */
    public function setTask(Task $task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set element
     *
     * @param Element $element
     *
     * @return TaskPermission
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
}
