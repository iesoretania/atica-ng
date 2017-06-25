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

namespace AppBundle\Service;

use AppBundle\Entity\Element;
use AppBundle\Entity\Organization;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Translation\TranslatorInterface;

class CoreData
{
    private $translator;
    private $entityManager;

    public function __construct(TranslatorInterface $translator, ObjectManager $entityManager)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    public function createOrganizationElements(Organization $organization, $rootName)
    {
        $data = [
            ['management', true],
            ['department', true],
            ['group', true],
            ['other', true],
            ['evaluation', false]
        ];

        $root = new Element();
        $root
            ->setOrganization($organization)
            ->setName($rootName);

        $this->entityManager->persist($root);

        foreach($data as $item) {
            $element = new Element();
            $element
                ->setOrganization($organization)
                ->setParent($root)
                ->setCode($item[0])
                ->setName($this->translator->trans('list.'.$item[0], [], 'core'));
            $this->entityManager->persist($element);
        }
        $this->entityManager->flush();
    }
}
