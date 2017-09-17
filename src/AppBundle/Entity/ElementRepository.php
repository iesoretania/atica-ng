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
     * @param boolean $final
     *
     * @return QueryBuilder
     */
    public function findAllSubProfilesQueryBuilder(array $elements, $final = false) {
        $collection = new ArrayCollection();

        foreach ($elements as $profile) {
            $children = $this->getChildren($profile, false, null, 'ASC', true);
            foreach ($children as $element) {
                if (!$collection->contains($element)) {
                    $collection->add($element);
                }
            }
        }

        $qb = $this->createQueryBuilder('e')
            ->andWhere('e IN (:profiles)')
            ->orderBy('e.left')
            ->setParameter('profiles', $collection);

        if ($final) {
            $qb = $qb
                ->andWhere('e.left = e.right - 1');
        }

        return $qb;
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
     * @param Folder $folder
     * @param integer $permission
     * @param boolean $final
     *
     * @return QueryBuilder
     */
    public function findAllProfilesByFolderPermissionQueryBuilder(Folder $folder, $permission, $final = false)
    {
        $profileElements = $this->getEntityManager()->createQuery(
            'SELECT e FROM AppBundle:Element e WHERE e IN (SELECT DISTINCT IDENTITY(fp.element) FROM AppBundle:Documentation\FolderPermission fp WHERE fp.folder = :folder AND fp.permission = :permission)')
            ->setParameter('folder', $folder)
            ->setParameter('permission', $permission)
            ->getResult();

        return $this->findAllSubProfilesQueryBuilder($profileElements, $final);
    }

    /**
     * @param Folder $folder
     * @param int $permission
     * @param User $user
     * @param boolean $final
     *
     * @return Element[]
     */
    public function findAllProfilesByFolderPermissionAndUser(Folder $folder, $permission, User $user, $final = false)
    {
        return $this->findAllProfilesByFolderPermissionQueryBuilder($folder, $permission, $final)
            ->innerJoin('AppBundle:Role', 'r', 'WITH', 'r.element = e')
            ->join('r.user', 'u')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()->getResult();
    }

    /**
     * @param Folder $folder
     * @param integer $permission
     * @param boolean $final
     *
     * @return Element[]
     */
    public function findAllProfilesByFolderPermission(Folder $folder, $permission, $final = false)
    {
        return $this->findAllProfilesByFolderPermissionQueryBuilder($folder, $permission, $final)->getQuery()->getResult();
    }

    /**
     * @param Element $element
     *
     * @return Element[]
     */
    public function findAllAncestorProfiles(Element $element) {
        $elements = [$element];

        while ($element->getParent() && $element->getProfile() === null) {
            $element = $element->getParent();
            $elements[] = $element;
        }

        return $elements;
    }

    /**
     * @param Element[] $elements
     * @return Element[]
     */
    public function findAllAncestorProfilesInArray(array $elements) {
        $results = [];

        foreach ($elements as $element) {
            $results[] = $this->findAllAncestorProfiles($element);
        }

        return array_unique(call_user_func_array('array_merge', $results));
    }

    /**
     * @param User $user
     * @param Organization $organization
     * @return Element[]
     */
    public function findAllProfilesByUserAndOrganization(User $user, Organization $organization)
    {
        $profiles = $this->getEntityManager()->createQuery(
            'SELECT e FROM AppBundle:Element e WHERE e.organization = :organization AND e.id IN (
                SELECT IDENTITY(r.element) FROM AppBundle:Role r WHERE r.user = :user
            )')
            ->setParameter('organization', $organization)
            ->setParameter('user', $user)
            ->getResult();

        return $this->getEntityManager()->getRepository('AppBundle:Element')
            ->findAllAncestorProfilesInArray($profiles);
    }
}
