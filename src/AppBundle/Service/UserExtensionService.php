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


use AppBundle\Entity\User;
use AppBundle\Security\OrganizationVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserExtensionService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(EntityManagerInterface $em, SessionInterface $session, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->em = $em;
        $this->session = $session;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getCurrentOrganization()
    {
        if ($this->session->has('organization_id')) {
            return $this->em->getRepository('AppBundle:Organization')->find($this->session->get('organization_id'));
        }
        return null;
    }

    public function checkCurrentOrganization(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($user->isGlobalAdministrator()) {
            return true;
        }

        return $this->session->has('organization_id')
                && $this->em->getRepository('AppBundle:Organization')->getMembershipByUserQueryBuilder($user)
                    ->andWhere('o = :organization')
                    ->setParameter('organization', $this->getCurrentOrganization())
                    ->getQuery()
                    ->getOneOrNullResult() !== null;
    }

    public function isUserGlobalAdministrator()
    {
        return $this->authorizationChecker->isGranted('ROLE_ADMIN');
    }

    public function isUserLocalAdministrator()
    {
        return $this->authorizationChecker->isGranted('ROLE_ADMIN')
            || $this->authorizationChecker->isGranted(OrganizationVoter::MANAGE, $this->getCurrentOrganization());
    }
}
