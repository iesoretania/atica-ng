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

namespace AppBundle\Controller\Organization;

use AppBundle\Security\OrganizationVoter;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/centro/listas")
 */
class ListController extends Controller
{
    /**
     * @Route("/listar/{page}/{rootName}", name="organization_list_list", requirements={"page" = "\d+"}, defaults={"page" = "1", "root" = null}, methods={"GET"})
     */
    public function listAction($page, $rootName = null, Request $request)
    {
        $organization = $this->get('AppBundle\Service\UserExtensionService')->getCurrentOrganization();
        $this->denyAccessUnlessGranted(OrganizationVoter::MANAGE, $organization);

        $em = $this->getDoctrine()->getManager();

        if (null === $rootName) {
            $rootElement = $em->getRepository('AppBundle:Element')->findCurrentOneByOrganization($organization);
        }
        else {
            if (null === $rootElement = $em->getRepository('AppBundle:Element')->findOneByOrganizationAndRootName($organization, $rootName)) {
                throw $this->createNotFoundException();
            }
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $em->getRepository('AppBundle:Element')->getChildrenQueryBuilder($rootElement, true)
            ->addSelect('ref')
            ->leftJoin('node.references', 'ref');

        $q = $request->get('q', null);
        if ($q) {
            $queryBuilder
                ->where('node.name LIKE :tq')
                ->orWhere('node.code LIKE :tq')
                ->orWhere('node.description LIKE :tq')
                ->setParameter('tq', '%'.$q.'%')
                ->setParameter('q', $q);
        }

        $adapter = new DoctrineORMAdapter($queryBuilder, false);
        $pager = new Pagerfanta($adapter);
        $pager
            ->setMaxPerPage($this->getParameter('page.size'))
            ->setCurrentPage($page);

        $title = $this->get('translator')->trans('title.list', [], 'list');

        return $this->render('organization/list/list.html.twig', [
            'title' => $title,
            'elements' => $pager->getIterator(),
            'pager' => $pager,
            'q' => $q,
            'domain' => 'list'
        ]);
    }
}
