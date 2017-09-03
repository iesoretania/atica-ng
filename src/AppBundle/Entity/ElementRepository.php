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

use AppBundle\Entity\Documentation\Folder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class ElementRepository extends NestedTreeRepository
{
    /**
     * @param Organization $organization
     * @return null|object
     */
    public function findCurrentOneByOrganization(Organization $organization)
    {
        $item = $organization->getElement() ?: $this->findOneBy(['organization' => $organization, 'parent' => null], ['left' => 'DESC']);

        return $item;
    }

    /**
     * @param Organization  $organization
     * @param string        $rootName
     * @return null|object
     */
    public function findOneByOrganizationAndRootName(Organization $organization, $rootName)
    {
        return $this->findOneBy(['organization' => $organization, 'name' => $rootName, 'parent' => null]);
    }

    /**
     * @param Organization  $organization
     * @param string        $path
     * @return null|object
     */
    public function findOneByOrganizationAndPath(Organization $organization, $path)
    {
        if (!$path) {
            return null;
        }

        $items = explode('/', $path);
        $itemName = array_shift($items);
        $current = null;

        while ($itemName) {
            $current = $this->findOneBy(['organization' => $organization, 'name' => $itemName, 'parent' => $current]);
            $itemName = array_shift($items);
        }

        return $current;
    }

    /**
     * @param Organization $organization
     * @param string $code
     * @return null|object
     */
    public function findOneByOrganizationAndCurrentCode(Organization $organization, $code)
    {
        $item = $this->findCurrentOneByOrganization($organization);

        if (null === $item) {
            return null;
        }

        return $this->getChildrenQueryBuilder($item)
            ->andWhere('node.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Element[] $elements
     * @return QueryBuilder
     */
    public function findAllSubProfilesQueryBuilder(array $elements) {
        $collection = new ArrayCollection();

        foreach ($elements as $profile) {
            $children = $this->getChildren($profile, false, null, 'ASC', true);
            foreach ($children as $element) {
                if (!$collection->contains($element)) {
                    $collection->add($element);
                }
            }
        }

        return $this->createQueryBuilder('e')
            ->where('e IN (:profiles)')
            ->orderBy('e.left')
            ->setParameter('profiles', $collection);
    }

    /**
     * @param Organization $organization
     * @return QueryBuilder
     */
    public function findAllProfilesByOrganizationQueryBuilder(Organization $organization)
    {
        $profileElements = $this->createQueryBuilder('e')
            ->innerJoin('e.profile', 'p')
            ->where('e.organization = :organization')
            ->setParameter('organization', $organization)
            ->getQuery()
            ->getResult();

        return $this->findAllSubProfilesQueryBuilder($profileElements);
    }

    /**
     * @param Organization $organization
     * @return Element[]
     */
    public function findAllProfilesByFolderPermission(Folder $folder, $permission)
    {
        $profileElements = $this->getEntityManager()->createQuery(
            'SELECT e FROM AppBundle:Element e WHERE e IN (SELECT DISTINCT IDENTITY(fp.element) FROM AppBundle:Documentation\FolderPermission fp WHERE fp.folder = :folder AND fp.permission = :permission)')
            ->setParameter('folder', $folder)
            ->setParameter('permission', $permission)
            ->getResult();

        return $this->findAllSubProfilesQueryBuilder($profileElements)->getQuery()->getResult();
    }
}
