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
use AppBundle\Entity\ElementRepository;
use AppBundle\Entity\Reference;
use AppBundle\Form\Type\ElementType;
use AppBundle\Security\OrganizationVoter;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
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

        /** @var ElementRepository $elementRepository */
        $elementRepository = $em->getRepository('AppBundle:Element');
        $element = $this->getSelectedElement($path, $elementRepository, $organization);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $elementRepository->getChildrenQueryBuilder($element, true)
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
     * @Route("/operar/{path}", name="organization_element_operation", requirements={"path" = ".+"}, defaults={"path" = null}, methods={"POST"})
     */
    public function operationAction($path = null, Request $request)
    {
        $organization = $this->get('AppBundle\Service\UserExtensionService')->getCurrentOrganization();
        $this->denyAccessUnlessGranted(OrganizationVoter::MANAGE, $organization);

        $em = $this->getDoctrine()->getManager();

        $elementRepository = $em->getRepository('AppBundle:Element');
        $element = $this->getSelectedElement($path, $elementRepository, $organization);

        $ok = false;
        if ($request->get('up')) {
            $item = $em->getRepository('AppBundle:Element')->find($request->get('up'));
            if (null === $item || $item->getParent() !== $element) {
                throw $this->createNotFoundException();
            }
            $em->getRepository('AppBundle:Element')->moveUp($item);
            $ok = true;
        }
        if ($request->get('down')) {
            $item = $em->getRepository('AppBundle:Element')->find($request->get('down'));
            if (null === $item || $item->getParent() !== $element) {
                throw $this->createNotFoundException();
            }
            $em->getRepository('AppBundle:Element')->moveDown($item);
            $ok = true;
        }

        $items = $request->request->get('elements', []);
        if ($ok || count($items) === 0) {
            return $this->redirectToRoute('organization_element_list', ['path' => $path]);
        }

        $elements = $em->createQueryBuilder()
            ->select('e')
            ->from('AppBundle:Element', 'e')
            ->where('e.id IN (:items)')
            ->andWhere('e.parent = :current')
            ->andWhere('e.code IS NULL')
            ->setParameter('items', $items)
            ->setParameter('current', $element)
            ->orderBy('e.left')
            ->getQuery()
            ->getResult();

        if ($request->get('confirm', '') === 'ok') {
            try {
                $em->createQueryBuilder()
                    ->delete('AppBundle:Element', 'e')
                    ->where('e.id IN (:items)')
                    ->andWhere('e.parent = :current')
                    ->andWhere('e.code IS NULL')
                    ->setParameter('items', $items)
                    ->setParameter('current', $element)
                    ->getQuery()
                    ->execute();

                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('message.deleted', [], 'element'));
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('message.delete_error', [], 'element'));
            }
            return $this->redirectToRoute('organization_element_list', ['path' => $path]);
        }

        $title = $this->get('translator')->trans('title.delete', [], 'element');
        $breadcrumb = $this->generateBreadcrumb($element, false);
        $breadcrumb[] = ['fixed' => $this->get('translator')->trans('title.delete', [], 'element')];

        return $this->render('organization/element/delete.html.twig', [
            'menu_path' => 'organization_element_list',
            'breadcrumb' => $breadcrumb,
            'title' => $title,
            'elements' => $elements
        ]);
    }

    /**
     * @Route("/carpeta/{path}", name="organization_element_folder_new", requirements={"path" = ".+"}, methods={"GET", "POST"})
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

        $new = in_array($request->get('_route'), ['organization_element_new', 'organization_element_folder_new'], true);
        $breadcrumb = $this->generateBreadcrumb($element, !$new);

        if ($new) {
            $newElement = new Element();
            $newElement
                ->setParent($element)
                ->setOrganization($organization)
                ->setFolder($request->get('_route') === 'organization_element_folder_new');

            $em->persist($newElement);

            $element = $newElement;

            $title = $this->get('translator')->trans($newElement->isFolder() ? 'title.new_folder' : 'title.new', [], 'element');
            $breadcrumb[] = ['fixed' => $title];
        } else {
            $title = $this->get('translator')->trans('title.edit', [], 'element');
        }

        $form = $this->createForm(ElementType::class, $element);
        $labels = $element->getLabels();
        $this->getElementReferences($element, $em, $labels, $form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->updateElementReferences($element, $em, $form);
                $this->addFlash('success', $this->get('translator')->trans('message.saved', [], 'element'));
                return $this->redirectToRoute('organization_element_list', ['page' => 1, 'path' => $element->getParent()->getPath()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('message.save_error', [], 'element'));
            }

        }

        return $this->render('organization/element/form.html.twig', [
            'menu_path' => 'organization_element_list',
            'breadcrumb' => $breadcrumb,
            'title' => $title,
            'element' => $element,
            'form' => $form->createView()
        ]);
    }

    /**
     * Returns breadcrumb that matches the element (ignores root element)
     * @param Element $element
     * @param bool $ignoreLast
     * @return array
     */
    private function generateBreadcrumb(Element $element = null, $ignoreLast = true)
    {
        $breadcrumb = [];

        if (null === $element) {
            return null;
        }

        $item = $element;
        while ($item->getParent()) {
            $entry = ['fixed' => $item->getName()];
            if ($item !== $element || !$ignoreLast) {
                $entry['routeName'] = 'organization_element_list';
                $entry['routeParams'] = ['path' => $item->getPath()];
            }
            array_unshift($breadcrumb, $entry);
            $item = $item->getParent();
        }
        return $breadcrumb;
    }

    /**
     * @param $path
     * @param ElementRepository $elementRepository
     * @param $organization
     * @return Element
     */
    private function getSelectedElement($path, ElementRepository $elementRepository, $organization)
    {
        /** @var Element|null $element */
        if (null !== $path) {
            $element = $elementRepository->findOneByOrganizationAndPath($organization, $path);
            if (null === $element) {
                throw $this->createNotFoundException();
            }
        } else {
            $element = $elementRepository->findCurrentOneByOrganization($organization);
        }
        return $element;
    }

    /**
     * @param Element $element
     * @param ObjectManager $em
     * @param Form $form
     */
    private function updateElementReferences($element, $em, $form)
    {
        /** @var Reference $reference */
        foreach ($element->getPathReferences() as $reference) {
            $items = $em->getRepository('AppBundle:Element')->getChildrenQueryBuilder($reference->getTarget())
                ->andWhere('node.folder = false')
                ->getQuery()
                ->getResult();

            $data = $form
                ->get('reference' . $reference->getTarget()->getId())->getData();

            if (!is_array($data)) {
                $data = [$data];
            }

            foreach ($items as $item) {
                $childItems = $em->getRepository('AppBundle:Element')->getChildrenQueryBuilder($element)
                    ->getQuery()
                    ->getResult();

                if (in_array($item, $data)) {
                    $element->addLabel($item);
                    /** @var Element $child */
                    foreach ($childItems as $child) {
                        $child->addLabel($item);
                    }
                } else {
                    $element->removeLabel($item);
                    /** @var Element $child */
                    foreach ($childItems as $child) {
                        $child->removeLabel($item);
                    }
                }
            }
        }
        $em->flush();
    }

    /**
     * @param Element $element
     * @param ObjectManager $em
     * @param Collection $labels
     * @param Form $form
     */
    private function getElementReferences($element, $em, $labels, $form)
    {
        /** @var Reference $reference */
        foreach ($element->getPathReferences() as $reference) {
            $data = [];
            $items = $em->getRepository('AppBundle:Element')->getChildrenQueryBuilder($reference->getTarget())
                ->andWhere('node.folder = false')
                ->getQuery()
                ->getResult();

            foreach ($labels as $label) {
                if (in_array($label, $items)) {
                    $data[] = $label;
                }
            }

            if (!empty($data)) {
                if ($reference->isMultiple()) {
                    $form->get('reference' . $reference->getTarget()->getId())->setData($data);
                } else {
                    $form->get('reference' . $reference->getTarget()->getId())->setData($data[0]);
                }
            }
        }
    }
}
