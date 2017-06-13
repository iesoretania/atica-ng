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

use AppBundle\Menu\MenuItem;

class CoreMenu implements MenuBuilderInterface
{
    private $userExtension;

    public function __construct(UserExtensionService $userExtension)
    {
        $this->userExtension = $userExtension;
    }

    public function getMenuStructure()
    {
        $isLocalAdministrator = $this->userExtension->isUserLocalAdministrator();

        $root = [];

        if ($isLocalAdministrator) {
            $menu = new MenuItem();
            $menu
                ->setName('admin')
                ->setRouteName('admin_menu')
                ->setCaption('menu.admin')
                ->setDescription('menu.admin.detail')
                ->setColor('teal')
                ->setIcon('wrench');

            $root[] = $menu;
        }

        return $root;
    }
}
