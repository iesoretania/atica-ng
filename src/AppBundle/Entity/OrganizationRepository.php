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

class OrganizationRepository extends EntityRepository
{
    public function getMembershipByUserQueryBuilder(User $user)
    {
        if ($user->isGlobalAdministrator()) {
            return $this->createQueryBuilder('o')
                ->orderBy('o.name');
        }

        return $this->createQueryBuilder('o')
            ->join('o.memberships', 'm')
            ->where('m.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.name');
    }

    public function count() {
        $query = $this->createQueryBuilder('o')->select('count(o)')->getQuery();
        return $query->getSingleScalarResult();
    }
}
