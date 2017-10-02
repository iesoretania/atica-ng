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
use AppBundle\Entity\Profile;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Form\Model\UnitImport;
use AppBundle\Form\Type\Import\UnitType;
use AppBundle\Security\OrganizationVoter;
use AppBundle\Utils\CsvImporter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class DepartmentController extends Controller
{
    /**
     * @Route("/centro/importar/departamentos", name="organization_import_department_form", methods={"GET", "POST"})
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

            $stats = $this->importDepartmentsFromCsv($formData->getFile()->getPathname(), $organization);

            if (null !== $stats) {
                $this->addFlash('success', $this->get('translator')->trans('message.import_ok', [], 'import'));
                $breadcrumb[] = ['fixed' => $this->get('translator')->trans('title.import_result', [], 'import')];
            } else {
                $this->addFlash('error', $this->get('translator')->trans('message.import_error', [], 'import'));
            }
        }
        $title = $this->get('translator')->trans('title.department_import', [], 'import');

        return $this->render('admin/organization/import/department_form.html.twig', [
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
    private function importDepartmentsFromCsv($file, Organization $organization)
    {
        $newDeptCount = 0;
        $existingDeptCount = 0;

        $em = $this->getDoctrine()->getManager();
        $base = $em->getRepository('AppBundle:Element')->findOneByOrganizationAndCurrentCode($organization, 'department');

        if (null === $base) {
            return null;
        }

        $importer = new CsvImporter($file, true);

        $deptCollection = [];

        try {
            /** @var Profile $headProfile */
            $headProfile = $em->getRepository('AppBundle:Profile')
                ->findOneByOrganizationAndCode($organization, 'department_head');

            if (null === $headProfile) {
                return null;
            }

            while ($data = $importer->get(100)) {
                foreach ($data as $userData) {
                    if (!isset($userData['Descripción'])) {
                        return null;
                    }
                    $deptName = $userData['Descripción'];

                    if (isset($deptCollection[$deptName])) {
                        $dept = $deptCollection[$deptName];
                    } else {
                        $dept = $em->getRepository('AppBundle:Element')->getChildrenQueryBuilder($base)
                            ->andWhere('node.name = :department')
                            ->setParameter('department', $deptName)
                            ->getQuery()
                            ->getOneOrNullResult();

                        if (null === $dept) {
                            $dept = new Element();
                            $dept
                                ->setOrganization($organization)
                                ->setParent($base)
                                ->setName($deptName)
                                ->setCode($deptName)
                                ->setLocked(false);

                            $em->persist($dept);

                            $newDeptCount++;
                        } else {
                            $existingDeptCount++;
                        }

                        $deptCollection[$deptName] = $dept;
                    }

                    $head = $userData['Jefe de departamento'];

                    if ($head) {
                        /** @var User|null $user */
                        $user = $em->getRepository('AppBundle:User')->findOneByOrganizationAndFullName($organization, $head, new \DateTime());
                        if ($user) {
                            $role = $em->getRepository('AppBundle:Role')->findOneBy([
                                'element' => $dept,
                                'profile' => $headProfile,
                                'user' => $user
                            ]);
                            if (null === $role) {
                                $role = new Role();
                                $role
                                    ->setElement($dept)
                                    ->setProfile($headProfile)
                                    ->setUser($user);
                                $em->persist($role);
                                $dept->addRole($role);
                            }
                        }
                    }
                }
            }
            $em->flush();
        } catch (Exception $e) {
            return null;
        }

        return [
            'new_dept_count' => $newDeptCount,
            'existing_dept_count' => $existingDeptCount,
            'dept_collection' => $deptCollection
        ];
    }
}
