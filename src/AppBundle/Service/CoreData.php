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
use AppBundle\Entity\Profile;
use AppBundle\Entity\Reference;
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
            ['management', false, []],
            ['department', true, []],
            ['unit', true, []],
            ['evaluation', false, []],
            ['subject', true, [
                ['department', false, false],
                ['unit', true, false],
                ['evaluation', true, true]
            ]],
            ['other', false, []],
        ];

        $elements = [];

        $root = new Element();
        $root
            ->setOrganization($organization)
            ->setFolder(true)
            ->setName($rootName);

        $this->entityManager->persist($root);

        foreach ($data as $item) {
            $element = new Element();
            $element
                ->setOrganization($organization)
                ->setParent($root)
                ->setCode($item[0])
                ->setFolder(true)
                ->setLocked(true)
                ->setName($this->translator->trans('list.'.$item[0], [], 'core'));

            $this->entityManager->persist($element);

            if ($item[1]) {
                $profile = new Profile();
                $profile
                    ->setOrganization($organization)
                    ->setCode($item[0])
                    ->setNameNeutral($this->translator->trans('profile.'.$item[0].'_neutral', [], 'core'))
                    ->setNameMale($this->translator->trans('profile.'.$item[0].'_male', [], 'core'))
                    ->setNameFemale($this->translator->trans('profile.'.$item[0].'_female', [], 'core'))
                    ->setInitials($this->translator->trans('profile.'.$item[0].'_initials', [], 'core'));

                $element->setProfile($profile);

                $this->entityManager->persist($profile);
            }

            $elements[$item[0]] = $element;

            if (!empty($item[2])) {
                foreach ($item[2] as $referenceData) {
                    $reference = new Reference();
                    $reference
                        ->setSource($element)
                        ->setTarget($elements[$referenceData[0]])
                        ->setMandatory($referenceData[1])
                        ->setMultiple($referenceData[2]);

                    $element->addReference($reference);
                    $this->entityManager->persist($reference);
                }
            }
        }
        $this->entityManager->flush();
    }
}
