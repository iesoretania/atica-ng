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

namespace AppBundle\Controller\Documentation;

use AppBundle\Entity\Documentation\Folder;
use AppBundle\Entity\Documentation\FolderRepository;
use AppBundle\Entity\ElementRepository;
use AppBundle\Entity\Organization;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BrowserController extends Controller
{
    /**
     * @Route("/documentos/{id}/{page}", name="documentation", requirements={"page" = "\d+", "id" = "\d+"}, defaults={"page" = "1", "folder" = null}, methods={"GET"})
     */
    public function browseAction($page, $id = null, Request $request)
    {
        $organization = $this->get('AppBundle\Service\UserExtensionService')->getCurrentOrganization();

        $q = $request->get('q', null);

        $folder = (null === $id) ? $this->getRootFolder($organization) : $this->getFolder($organization, $id);

        if (null === $folder || $folder->getOrganization() !== $organization) {
            throw $this->createNotFoundException();
        }

        $pager = $this->getFolderEntriesPager($page, $folder, $q);

        $breadcrumb = $this->generateBreadcrumb($folder);

        return $this->render('documentation/list.html.twig', [
            'breadcrumb' => $breadcrumb,
            'pager' => $pager,
            'current' => $folder,
            'tree' => $this->getOrganizationTree($this->getRootFolder($organization), $folder),
            'q' => $q,
            'domain' => 'element'
        ]);
    }

    /**
     * @param Organization $organization
     * @return Folder
     */
    private function getRootFolder($organization)
    {
        /** @var FolderRepository $folderRepository */
        $folderRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Documentation\Folder');

        /** @var Folder|null $folder */
        $folder = $folderRepository->findOneBy(['organization' => $organization, 'parent' => null]);

        return $folder;
    }

    /**
     * @param Organization $organization
     * @param int $id
     * @return Folder
     */
    private function getFolder($organization, $id)
    {
        /** @var FolderRepository $folderRepository */
        $folderRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Documentation\Folder');

        /** @var Folder|null $folder */
        $folder = $folderRepository->findOneBy(['organization' => $organization, 'id' => $id]);

        return $folder;
    }

    /**
     * @param $page
     * @param Folder|null $folder
     * @param $q
     * @return Pagerfanta
     */
    private function getFolderEntriesPager($page, Folder $folder = null, $q)
    {
        /** @var ElementRepository $entriesRepository */
        $entriesRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Documentation\Entry');

        // obtener las carpetas
        $folders = $this->getDoctrine()->getManager()->getRepository('AppBundle:Documentation\Folder')->getChildren($folder, false);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $entriesRepository->createQueryBuilder('e')
            ->andWhere('e.folder IN (:folders)')
            ->setParameter('folders', $folders);

        if ($q) {
            $queryBuilder
                ->andWhere('e.name LIKE :tq')
                ->setParameter('tq', '%'.$q.'%');
        }

        $adapter = new DoctrineORMAdapter($queryBuilder, false);
        $pager = new Pagerfanta($adapter);
        $pager
            ->setMaxPerPage($this->getParameter('page.size'))
            ->setCurrentPage($q ? 1 : $page);

        return $pager;
    }

    /**
     * Returns breadcrumb that matches the folder (ignores root element)
     * @param Folder $folder
     * @param bool $ignoreLast
     * @return array
     */
    private function generateBreadcrumb(Folder $folder = null, $ignoreLast = true)
    {
        $breadcrumb = [];

        if (null === $folder) {
            return null;
        }

        $item = $folder;
        while ($item->getParent()) {
            $entry = ['fixed' => $item->getName()];
            if ($item !== $folder || !$ignoreLast) {
                $entry['routeName'] = 'documentation';
                $entry['routeParams'] = ['id' => $item->getId()];
            }
            array_unshift($breadcrumb, $entry);
            $item = $item->getParent();
        }
        return $breadcrumb;
    }

    /**
     * Returns folder tree
     *
     * @param Folder $folder
     * @param Folder $current
     *
     * @return array
     */
    private function getOrganizationTree(Folder $folder, Folder $current = null)
    {
        /** @var FolderRepository $folderRepository */
        $folderRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Documentation\Folder');
        $children = $folderRepository->childrenHierarchy($folder);

        $parents = [];
        if ($current)
        {
            $parent = $current->getParent();
            while ($parent) {
                $parents[] = $parent->getId();
                $parent = $parent->getParent();
            }
        }

        $tree = $this->processChildren($children, $current ? $current->getId() : null, $parents);

        return $tree;
    }

    /**
     * Convert children array into a treeview array
     *
     * @param array $children
     * @param integer $currentId
     * @param array $parentsId
     * @return array
     */
    private function processChildren(array $children, $currentId = null, $parentsId = [])
    {
        $result = [];
        foreach($children as $child) {
            $item = [];
            $item['text'] = $child['name'];
            if ($currentId === $child['id']) {
                $item['state'] = ['selected' => true, 'expanded' => true];
            }
            if ($parentsId && in_array($child['id'], $parentsId)) {
                $item['state'] = ['expanded' => true];
            }
            if (count($child['__children']) > 0) {
                $item['nodes'] = $this->processChildren($child['__children'], $currentId, $parentsId);
            } else {
                $item['icon'] = 'fa fa-folder';
            }
            $item['href'] = $this->generateUrl('documentation', ['id' => $child['id']]);
            $result[] = $item;
        }

        return $result;
    }
}