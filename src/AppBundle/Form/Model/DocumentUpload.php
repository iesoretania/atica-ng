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

use AppBundle\Entity\Element;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class DocumentUpload
{
    /**
     * @Assert\File
     * @var UploadedFile
     */
    private $file;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \DateTime
     */
    private $createDate;

    /**
     * @Assert\Range(min = 0)
     * @var int
     */
    private $version;

    /**
     * @var Element
     */
    private $uploadProfile;

    public function __construct()
    {
        $this->version = 0;
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
     * @return DocumentUpload
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return DocumentUpload
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return DocumentUpload
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param \DateTime $createDate
     *
     * @return DocumentUpload
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     *
     * @return DocumentUpload
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return Element
     */
    public function getUploadProfile()
    {
        return $this->uploadProfile;
    }

    /**
     * @param Element $uploadProfile
     *
     * @return DocumentUpload
     */
    public function setUploadProfile($uploadProfile)
    {
        $this->uploadProfile = $uploadProfile;
        return $this;
    }
}
