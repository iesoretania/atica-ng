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

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Membership;
use AppBundle\Entity\Organization;
use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadInitialUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function load(ObjectManager $manager)
    {
        $organization = new Organization();
        $organization
            ->setName('I.E.S. Test')
            ->setShortName('test');

        $manager->persist($organization);

        $userAdmin = new User();
        $userAdmin
            ->setLoginUsername('admin')
            ->setFirstName('Admin')
            ->setLastName('Admin')
            ->setGender(User::GENDER_NEUTRAL)
            ->setEnabled(true)
            ->setGlobalAdministrator(true)
            ->setPassword($this->container->get('security.password_encoder')->encodePassword($userAdmin, 'admin'));

        $manager->persist($userAdmin);

        $membership = new Membership();
        $membership
            ->setOrganization($organization)
            ->setUser($userAdmin)
            ->setValidFrom(new \DateTime('2001/01/01 00:00:00'));

        $manager->persist($membership);

        $manager->flush();
    }

    public function getOrder()
    {
        return 10;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
