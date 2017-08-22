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

use AppBundle\Entity\Actor;
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

    /**
     * Crea los elementos básicos de una organización
     *
     * @param Organization $organization
     *
     * @return Element
     */
    public function createOrganizationElements(Organization $organization)
    {
        $profilesData = [
            'teacher' => [],
            'tutor' => [],
            'department_head' => [],
            'financial_manager' => [],
            'student' => [],
            'head_teacher' => [],
            'staff' => []
        ];

        $profiles = [];

        foreach($profilesData as $key => $profileData) {
            $profile = new Profile();
            $profile
                ->setOrganization($organization)
                ->setCode($key)
                ->setNameNeutral($this->translator->trans('profile.' . $key . '.0', [], 'core'))
                ->setNameMale($this->translator->trans('profile.' . $key . '.1', [], 'core'))
                ->setNameFemale($this->translator->trans('profile.' . $key . '.2', [], 'core'))
                ->setInitials($this->translator->trans('profile.' . $key . '.initials', [], 'core'));

            $profiles[$key] = $profile;

            $this->entityManager->persist($profile);
        }

        $data = [
            'management' => [false, [], []],
            'department' => ['department_head', [], []],
            'unit' => ['tutor', [], [
                'tutor',
                'student'
            ]],
            'evaluation' => [false, [], []],
            'subject' => ['teacher', [
                'department' => [false, false],
                'unit' => [true, false],
                'evaluation' => [true, true]
            ], [
                'teacher',
                'student']
            ],
            'other' => [false, [], []]
        ];

        $elements = [];

        $root = new Element();
        $root
            ->setOrganization($organization)
            ->setFolder(true)
            ->setName($organization->getName());

        $this->entityManager->persist($root);

        foreach ($data as $key => $item) {
            $element = new Element();
            $element
                ->setOrganization($organization)
                ->setParent($root)
                ->setCode($key)
                ->setFolder(true)
                ->setLocked(true)
                ->setName($this->translator->trans('list.' . $key, [], 'core'));

            $this->entityManager->persist($element);

            if (false !== $item[0]) {
                $element->setProfile($profiles[$item[0]]);
            }

            $elements[$key] = $element;
        }

        // referencias
        foreach ($data as $key => $item) {
            foreach ($item[1] as $name => $referenceData) {
                $reference = new Reference();
                $reference
                    ->setSource($elements[$key])
                    ->setTarget($elements[$name])
                    ->setMandatory($referenceData[0])
                    ->setMultiple($referenceData[1]);

                $elements[$key]->addReference($reference);
                $this->entityManager->persist($reference);
            }
        }

        // actores
        foreach ($data as $key => $item) {
            foreach ($item[2] as $actorData) {
                $actor = new Actor();
                $actor
                    ->setSource($elements[$key])
                    ->setProfile($profiles[$actorData]);
                $elements[$key]->addActor($actor);

                $this->entityManager->persist($actor);
            }
        }

        return $root;
    }
}
