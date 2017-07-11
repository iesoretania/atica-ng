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

use AppBundle\Entity\Element;
use AppBundle\Form\Type\ElementType;
use AppBundle\Security\OrganizationVoter;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/centro/elementos")
 */
class ElementController extends Controller
{
    /**
     * @Route("/listar/{page}/{path}", name="organization_element_list", requirements={"page" = "\d+", "path" = ".+"}, defaults={"page" = "1", "path" = null}, methods={"GET"})
     */
    public function listAction($page, $path = null, Request $request)
    {
        $organization = $this->get('AppBundle\Service\UserExtensionService')->getCurrentOrganization();
        $this->denyAccessUnlessGranted(OrganizationVoter::MANAGE, $organization);

        $em = $this->getDoctrine()->getManager();

        /** @var Element|null $element */
        if (null === $path) {
            $element = $em->getRepository('AppBundle:Element')->findCurrentOneByOrganization($organization);
        }
        else {
            if (null === $element = $em->getRepository('AppBundle:Element')->findOneByOrganizationAndPath($organization, $path)) {
                throw $this->createNotFoundException();
            }
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $em->getRepository('AppBundle:Element')->getChildrenQueryBuilder($element, true)
            ->addSelect('ref')
            ->leftJoin('node.references', 'ref');

        $q = $request->get('q', null);
        if ($q) {
            $queryBuilder
                ->andWhere('node.name LIKE :tq')
                ->setParameter('tq', '%'.$q.'%');
        }

        $adapter = new DoctrineORMAdapter($queryBuilder, false);
        $pager = new Pagerfanta($adapter);
        $pager
            ->setMaxPerPage($this->getParameter('page.size'))
            ->setCurrentPage($page);

        $breadcrumb = $this->generateBreadcrumb($element);

        return $this->render('organization/element/list.html.twig', [
            'breadcrumb' => $breadcrumb,
            'title' => $element->getName(),
            'elements' => $pager->getIterator(),
            'pager' => $pager,
            'current' => $element,
            'q' => $q,
            'domain' => 'element'
        ]);
    }

    /**
     * @Route("/nuevo/{path}", name="organization_element_new", requirements={"path" = ".+"}, methods={"GET", "POST"})
     * @Route("/modificar/{path}", name="organization_element_form", requirements={"path" = ".+"}, methods={"GET", "POST"})
     */
    public function formAction($path, Request $request)
    {
        $organization = $this->get('AppBundle\Service\UserExtensionService')->getCurrentOrganization();
        $this->denyAccessUnlessGranted(OrganizationVoter::MANAGE, $organization);

        $em = $this->getDoctrine()->getManager();

        /** @var Element|null $element */
        if (null === $element = $em->getRepository('AppBundle:Element')->findOneByOrganizationAndPath($organization, $path)) {
            throw $this->createNotFoundException();
        }

        $new = $request->get('_route') === 'organization_element_new';
        $breadcrumb = $this->generateBreadcrumb($element, !$new);

        if ($new) {
            $newElement = new Element();
            $newElement
                ->setParent($element)
                ->setFolder(false);

            $em->persist($newElement);

            $element = $newElement;
            $breadcrumb[] = ['fixed' => $this->get('translator')->trans('title.new', [], 'element')];
        }

        $form = $this->createForm(ElementType::class, $element);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('message.saved', [], 'element'));
                return $this->redirectToRoute('organization_element_list', ['page' => 1, 'path' => $element->getParent()->getPath()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('message.save_error', [], 'element'));
            }
        }

        $title = $this->get('translator')->trans($element->getId() ? 'title.edit' : 'title.new', [], 'element');

        return $this->render('organization/element/form.html.twig', [
            'menu_path' => 'organization_element_list',
            'breadcrumb' => $breadcrumb,
            'title' => $title,
            'element' => $element,
            'form' => $form->createView()
        ]);
    }

    /**
     * Returns breadcrumb that matches the element
     * @param Element $element
     * @param bool $ignoreLast
     * @return array
     */
    private function generateBreadcrumb(Element $element, $ignoreLast = true)
    {
        $breadcrumb = [];

        $item = $element;
        do {
            $entry = ['fixed' => $item->getName()];
            if ($item !== $element || !$ignoreLast) {
                $entry['routeName'] = 'organization_element_list';
                $entry['routeParams'] = ['page' => 1, 'path' => $item->getPath()];
            }
            array_unshift($breadcrumb, $entry);
            $item = $item->getParent();
        } while ($item);
        return $breadcrumb;
    }
}
