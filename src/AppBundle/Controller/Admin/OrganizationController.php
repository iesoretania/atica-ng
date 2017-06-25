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

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Organization;
use AppBundle\Form\Type\OrganizationType;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/organizaciones")
 */
class OrganizationController extends Controller
{
    /**
     * @Route("/nueva", name="admin_organization_form_new", methods={"GET", "POST"})
     * @Route("/{id}", name="admin_organization_form_edit", requirements={"id" = "\d+"}, methods={"GET", "POST"})
     */
    public function indexAction(Organization $organization = null, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if (null === $organization) {
            $organization = new Organization();
            $em->persist($organization);
        }

        $form = $this->createForm(OrganizationType::class, $organization, [
            'new' => $organization->getId() === null
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('message.saved', [], 'organization'));
                return $this->redirectToRoute('admin_organization_list');
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('message.save_error', [], 'organization'));
            }
        }

        $title = $this->get('translator')->trans($organization->getId() ? 'title.edit' : 'title.new', [], 'organization');

        $breadcrumb = [];

        if ($organization->getId()) {
            $breadcrumb[] = ['fixed' => (string) $organization];
        } else {
            $breadcrumb[] = ['fixed' => $this->get('translator')->trans('title.new', [], 'organization')];
        }

        return $this->render('organization/form.html.twig', [
            'menu_path' => 'admin_organization_list',
            'breadcrumb' => $breadcrumb,
            'title' => $title,
            'form' => $form->createView(),
            'user' => $organization
        ]);
    }

    /**
     * @Route("/listar/{page}", name="admin_organization_list", requirements={"page" = "\d+"}, defaults={"page" = "1"}, methods={"GET"})
     */
    public function listAction($page, Request $request)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder
            ->select('o')
            ->from('AppBundle:Organization', 'o');

        $q = $request->get('q', null);
        if ($q) {
            $queryBuilder
                ->where('o.id = :q')
                ->orWhere('o.name LIKE :tq')
                ->orWhere('o.code LIKE :tq')
                ->orWhere('o.emailAddress LIKE :tq')
                ->orWhere('o.phoneNumber LIKE :tq')
                ->orWhere('o.city LIKE :tq')
                ->setParameter('tq', '%'.$q.'%')
                ->setParameter('q', $q);
        }

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pager = new Pagerfanta($adapter);
        $pager
            ->setMaxPerPage($this->getParameter('page.size'))
            ->setCurrentPage($page);

        $title = $this->get('translator')->trans('title.list', [], 'organization');

        return $this->render('organization/list.html.twig', [
            'title' => $title,
            'organization' => $pager->getIterator(),
            'pager' => $pager,
            'q' => $q,
            'domain' => 'organization'
        ]);
    }

    /**
     * @Route("/eliminar", name="admin_organization_delete", methods={"POST"})
     */
    public function deleteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $em->createQueryBuilder();

        $items = $request->request->get('organizations', []);
        if (count($items) === 0) {
            return $this->redirectToRoute('admin_organization_list');
        }

        $organizations = $queryBuilder
            ->select('o')
            ->from('AppBundle:Organization', 'o')
            ->where('o.id IN (:items)')
            ->andWhere('o.id != :current')
            ->setParameter('items', $items)
            ->setParameter('current', $this->get('session')->get('organization_id'), '')
            ->orderBy('o.code')
            ->getQuery()
            ->getResult();

        if ($request->get('confirm', '') === 'ok') {
            try {
                /* Borrar primero las pertenencias */
                $em->createQueryBuilder()
                    ->delete('AppBundle:Membership', 'm')
                    ->where('m.organization IN (:items)')
                    ->setParameter('items', $items)
                    ->getQuery()
                    ->execute();

                $em->createQueryBuilder()
                    ->delete('AppBundle:Organization', 'o')
                    ->where('o IN (:items)')
                    ->setParameter('items', $items)
                    ->getQuery()
                    ->execute();

                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('message.deleted', [], 'organization'));
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('message.delete_error', [], 'organization'));
            }
            return $this->redirectToRoute('admin_organization_list');
        }

        $title = $this->get('translator')->trans('title.delete', [], 'organization');
        $breadcrumb = [['fixed' => $this->get('translator')->trans('title.delete', [], 'organization')]];

        return $this->render('organization/delete.html.twig', [
            'menu_path' => 'admin_organization_list',
            'breadcrumb' => $breadcrumb,
            'title' => $title,
            'organizations' => $organizations
        ]);
    }
}
