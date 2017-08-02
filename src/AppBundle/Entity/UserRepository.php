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

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    /**
     * Loads the user for the given username.
     *
     * This method must return null if the user is not found.
     *
     * @param string $username The username
     *
     * @return UserInterface|null
     */
    public function loadUserByUsername($username) {
        if (!$username) {
            return null;
        }
        return $this->getEntityManager()
            ->createQuery('SELECT u FROM AppBundle:User u
                           WHERE u.loginUsername = :username
                           OR u.emailAddress = :username')
            ->setParameters([
                'username' => $username
            ])
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param UserInterface $user
     * @return null|UserInterface
     */
    public function refreshUser(UserInterface $user) {
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param $class
     * @return bool
     */
    public function supportsClass($class) {
        return $class === User::class;
    }

    /**
     * @param Organization $organization
     * @param \DateTime|null $date
     * @return array
     */
    public function findByOrganizationAndDate(Organization $organization, $date = null)
    {
        $query = $this->createQueryBuilder('u')
            ->distinct()
            ->join('u.memberships', 'm')
            ->where('m.organization = :organization')
            ->setParameter('organization', $organization);

        if ($date) {
            $query = $query
                ->andWhere('m.validUntil >= :date')
                ->orWhere('m.validUntil IS NULL')
                ->andWhere('m.validFrom <= :date')
                ->setParameter('date', $date);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param Organization $organization
     * @param string $fullName
     * @param \DateTime|null $fullName
     * @return User|null
     */
    public function findOneByOrganizationAndFullName(Organization $organization, $fullName, $date = null)
    {
        $item = explode(', ', $fullName);

        return $this->createQueryBuilder('u')
            ->distinct()
            ->where('u.firstName = :firstName')
            ->andWhere('u.lastName = :lastName')
            ->join('u.memberships', 'm')
            ->andWhere('m.organization = :organization')
            ->setParameter('organization', $organization)
            ->setParameter('firstName', $item[1])
            ->setParameter('lastName', $item[0])
            ->andWhere('m.validUntil >= :date OR    m.validUntil IS NULL')
            ->andWhere('m.validFrom <= :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
