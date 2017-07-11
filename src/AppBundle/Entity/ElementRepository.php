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

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class ElementRepository extends NestedTreeRepository
{
    /**
     * @param Organization $organization
     * @return null|object
     */
    public function findCurrentOneByOrganization(Organization $organization)
    {
        $item = $organization->getCurrentElement() ?: $this->findOneBy(['organization' => $organization, 'parent' => null], ['left' => 'DESC']);

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
}
