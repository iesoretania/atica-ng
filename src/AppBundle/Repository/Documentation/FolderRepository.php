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

namespace AppBundle\Repository\Documentation;

use AppBundle\Entity\Documentation\FolderPermission;
use AppBundle\Entity\Organization;
use AppBundle\Entity\User;

class FolderRepository extends \Gedmo\Tree\Entity\Repository\NestedTreeRepository
{
    public function getAccessDeniedFoldersForUserAndOrganizationArray(User $user, Organization $organization)
    {
        $userProfiles = $this->getEntityManager()->getRepository('AppBundle:Element')
            ->findAllProfilesByUserAndOrganization($user, $organization);

        $restrictedFolders = $this->getEntityManager()->createQuery('
                SELECT f FROM AppBundle:Documentation\Folder f WHERE f NOT IN (
                  SELECT f2 FROM AppBundle:Documentation\Folder f2 JOIN AppBundle:Documentation\FolderPermission fp WITH fp.folder = f2 WHERE fp.permission = :permission AND fp.element IN (:elements)
                ) AND f IN (
                  SELECT DISTINCT f3 FROM AppBundle:Documentation\Folder f3 JOIN AppBundle:Documentation\FolderPermission fp2 WITH fp2.folder = f3 WHERE fp2.permission = :permission
                ) AND f.organization = :organization
            ')
            ->setParameter('permission', FolderPermission::PERMISSION_VISIBLE)
            ->setParameter('elements', $userProfiles)
            ->setParameter('organization', $organization)
            ->getResult();

        return $restrictedFolders;
    }
}
