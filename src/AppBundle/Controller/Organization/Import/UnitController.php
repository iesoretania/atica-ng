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

namespace AppBundle\Controller\Organization\Import;

use AppBundle\Entity\Element;
use AppBundle\Entity\Organization;
use AppBundle\Entity\User;
use AppBundle\Form\Model\UnitImport;
use AppBundle\Form\Type\Import\UnitType;
use AppBundle\Security\OrganizationVoter;
use AppBundle\Utils\CsvImporter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class UnitController extends Controller
{
    /**
     * @Route("/centro/importar/unidades", name="organization_import_unit_form", methods={"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $organization = $this->get('AppBundle\Service\UserExtensionService')->getCurrentOrganization();
        $this->denyAccessUnlessGranted(OrganizationVoter::MANAGE, $organization);

        $formData = new UnitImport();
        $form = $this->createForm(UnitType::class, $formData);
        $form->handleRequest($request);

        $stats = null;
        $breadcrumb = [];

        if ($form->isSubmitted() && $form->isValid()) {

            $stats = $this->importUnitsFromCsv($formData->getFile()->getPathname(), $organization);

            if (null !== $stats) {
                $this->addFlash('success', $this->get('translator')->trans('message.import_ok', [], 'import'));
                $breadcrumb[] = ['fixed' => $this->get('translator')->trans('title.import_result', [], 'import')];
            } else {
                $this->addFlash('error', $this->get('translator')->trans('message.import_error', [], 'import'));
            }
        }
        $title = $this->get('translator')->trans('title.unit_import', [], 'import');

        return $this->render('admin/organization/import/unit_form.html.twig', [
            'title' => $title,
            'breadcrumb' => $breadcrumb,
            'form' => $form->createView(),
            'stats' => $stats
        ]);
    }

    /**
     * @param string $file
     * @param Organization $organization
     * @return array|null
     */
    private function importUnitsFromCsv($file, Organization $organization)
    {
        $newUnitCount = 0;
        $existingUnitCount = 0;

        $em = $this->getDoctrine()->getManager();
        $base = $em->getRepository('AppBundle:Element')->findOneByOrganizationAndCurrentCode($organization, 'unit');

        if (null === $base) {
            return null;
        }

        $importer = new CsvImporter($file, true);

        $unitCollection = [];

        try {
            while ($data = $importer->get(100)) {
                foreach ($data as $userData) {
                    if (!isset($userData['Unidad'])) {
                        return null;
                    }
                    $unitName = $userData['Unidad'];

                    if (isset($unitCollection[$unitName])) {
                        $unit = $unitCollection[$unitName];
                    }
                    else {
                        $unit = $em->getRepository('AppBundle:Element')->getChildrenQueryBuilder($base)
                            ->andWhere('node.name = :unit')
                            ->setParameter('unit', $unitName)
                            ->getQuery()
                            ->getOneOrNullResult();

                        if (null === $unit) {
                            $unit = new Element();
                            $unit
                                ->setOrganization($organization)
                                ->setParent($base)
                                ->setName($unitName);
                            $em->persist($unit);

                            $newUnitCount++;
                        } else {
                            $existingUnitCount++;
                        }

                        $unitCollection[$unitName] = $unit;
                    }

                    preg_match_all('/\b(.*) \(.*\)/U', $userData['Tutor/a'], $matches, PREG_SET_ORDER, 0);

                    foreach($matches as $tutor) {
                        /** @var User|null $user */
                        $user = $em->getRepository('AppBundle:User')->findOneByOrganizationAndFullName($organization, $tutor[1]);
                        if ($user) {
                            $user->addElement($unit);
                        }
                    }
                }
            }
            $em->flush();
        } catch (Exception $e) {
            return null;
        }

        return [
            'new_unit_count' => $newUnitCount,
            'existing_unit_count' => $existingUnitCount,
            'unit_collection' => $unitCollection
        ];
    }
}
