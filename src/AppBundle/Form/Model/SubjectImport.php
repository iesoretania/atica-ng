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

namespace AppBundle\Form\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class SubjectImport
{
    /**
     * @Assert\File
     * @var UploadedFile
     */
    private $file;

    /**
     * @var boolean
     */
    private $removeExistingTeachers;

    /**
     * @var boolean
     */
    private $addNewTeachers;

    /**
     * SubjectImport constructor.
     */
    public function __construct()
    {
        $this->removeExistingTeachers = true;
        $this->addNewTeachers = true;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     * @return SubjectImport
     */
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRemoveExistingTeachers()
    {
        return $this->removeExistingTeachers;
    }

    /**
     * @param bool $removeExistingTeachers
     * @return SubjectImport
     */
    public function setRemoveExistingTeachers($removeExistingTeachers)
    {
        $this->removeExistingTeachers = $removeExistingTeachers;
        return $this;
    }

    /**
     * Get addNewTeachers
     *
     * @return bool
     */
    public function getAddNewTeachers()
    {
        return $this->addNewTeachers;
    }

    /**
     * Set addNewTeachers
     *
     * @param bool $addNewTeachers
     *
     * @return SubjectImport
     */
    public function setAddNewTeachers($addNewTeachers)
    {
        $this->addNewTeachers = $addNewTeachers;
        return $this;
    }


}
