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
use AppBundle\Entity\ElementRepository;
use AppBundle\Entity\Organization;
use AppBundle\Entity\Profile;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Form\Model\SubjectImport;
use AppBundle\Form\Type\Import\SubjectType;
use AppBundle\Security\OrganizationVoter;
use AppBundle\Utils\CsvImporter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class SubjectController extends Controller
{
    /**
     * @Route("/centro/importar/asignaturas", name="organization_import_subject_form", methods={"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $organization = $this->get('AppBundle\Service\UserExtensionService')->getCurrentOrganization();
        $this->denyAccessUnlessGranted(OrganizationVoter::MANAGE, $organization);

        $formData = new SubjectImport();
        $form = $this->createForm(SubjectType::class, $formData);
        $form->handleRequest($request);

        $stats = null;
        $breadcrumb = [];

        if ($form->isSubmitted() && $form->isValid()) {

            $stats = $this->importSubjectsFromCsv($formData->getFile()->getPathname(), $organization, [
                'add_new_teachers' => $formData->getAddNewTeachers(),
                'remove_existing_teachers' => $formData->getRemoveExistingTeachers()
            ]);

            if (null !== $stats) {
                $this->addFlash('success', $this->get('translator')->trans('message.import_ok', [], 'import'));
                $breadcrumb[] = ['fixed' => $this->get('translator')->trans('title.import_result', [], 'import')];
            } else {
                $this->addFlash('error', $this->get('translator')->trans('message.import_error', [], 'import'));
            }
        }
        $title = $this->get('translator')->trans('title.subject_import', [], 'import');

        return $this->render('admin/organization/import/subject_form.html.twig', [
            'title' => $title,
            'breadcrumb' => $breadcrumb,
            'form' => $form->createView(),
            'stats' => $stats
        ]);
    }

    /**
     * @param string $file
     * @param Organization $organization
     * @param array $options
     * @return array|null
     */
    private function importSubjectsFromCsv($file, Organization $organization, array $options = [])
    {
        $addNewTeachers = isset($options['add_new_teachers']) && $options['add_new_teachers'];
        $removeExistingTeachers = isset($options['remove_existing_teachers']) && $options['remove_existing_teachers'];

        $newCount = 0;
        $existingCount = 0;

        $em = $this->getDoctrine()->getManager();

        /** @var ElementRepository $elementRepository */
        $elementRepository = $em->getRepository('AppBundle:Element');

        $baseSubject = $elementRepository->findOneByOrganizationAndCurrentCode($organization, 'subject');
        $baseUnit = $elementRepository->findOneByOrganizationAndCurrentCode($organization, 'unit');

        if (null === $baseSubject || null === $baseUnit) {
            return null;
        }

        $importer = new CsvImporter($file, true);

        $unitCollection = [];
        $unitFolderCollection = [];
        $collection = [];

        $teacherCollection = [];
        $teacherCache = [];

        try {
            /** @var Profile $teacherProfile */
            $teacherProfile = $em->getRepository('AppBundle:Profile')
                ->findOneByOrganizationAndCode($organization, 'teacher');

            if (null === $teacherProfile) {
                return null;
            }

            while ($data = $importer->get(100)) {
                foreach ($data as $userData) {
                    if (!isset($userData['Unidad'], $userData['Materia'], $userData['Profesor/a'])) {
                        return null;
                    }
                    $unitName = $userData['Unidad'];

                    if (isset($unitCollection[$unitName])) {
                        $unit = $unitCollection[$unitName];
                    } else {
                        $unit = $elementRepository->getChildrenQueryBuilder($baseUnit)
                            ->andWhere('node.code = :unit')
                            ->setParameter('unit', $unitName)
                            ->getQuery()
                            ->getOneOrNullResult();

                        $unitCollection[$unitName] = $unit;
                    }

                    if ($unit) {
                        $subjectName = $userData['Unidad'].' - '.$userData['Materia'];
                        $displayName = $unit->getName().' - '.$userData['Materia'];

                        $new = false;
                        $subject = null;

                        if (!isset($teacherCollection[$subjectName]['subject'])) {
                            if (isset($unitFolderCollection[$unitName])) {
                                $unitFolder = $unitFolderCollection[$unitName];
                            } else {
                                $unitFolder = $elementRepository->getChildrenQueryBuilder($baseSubject)
                                    ->andWhere('node.code = :unit')
                                    ->setParameter('unit', $unitName)
                                    ->getQuery()
                                    ->getOneOrNullResult();

                                if (null === $unitFolder) {
                                    $unitFolder = new Element();
                                    $unitFolder
                                        ->setOrganization($organization)
                                        ->setParent($baseSubject)
                                        ->setName($unit->getName())
                                        ->setCode($unit->getCode())
                                        ->setFolder(true);

                                    $unitFolder->addLabel($unit);

                                    $em->persist($unitFolder);

                                    $new = true;
                                }
                                $unitFolderCollection[$unitName] = $unitFolder;
                            }

                            if (!$new) {
                                $subject = $elementRepository->getChildrenQueryBuilder($baseSubject)
                                    ->andWhere('node.code = :subject')
                                    ->setParameter('subject', $subjectName)
                                    ->leftJoin('node.labels', 'l')
                                    ->andWhere('l = :unit')
                                    ->setParameter('unit', $unit)
                                    ->getQuery()
                                    ->getOneOrNullResult();
                            }

                            if (null === $subject) {
                                $subject = new Element();
                                $subject
                                    ->setOrganization($organization)
                                    ->setParent($unitFolder)
                                    ->setName($displayName)
                                    ->setCode($subjectName)
                                    ->setFolder(false)
                                    ->setLocked(false);

                                $em->persist($subject);
                                $newCount++;
                            } else {
                                $existingCount++;
                            }
                            $subject->addLabel($unit);
                            $teacherCollection[$subjectName] = ['subject' => $subject, 'teachers' => []];
                            $collection[] = $subject;
                        }

                        $tutor = $userData['Profesor/a'];

                        if (isset($teacherCache[$tutor]) && !in_array($teacherCache[$tutor], $teacherCollection[$subjectName]['teachers'])) {
                            $teacherCollection[$subjectName]['teachers'][] = $teacherCache[$tutor];
                        } else {
                            /** @var User|null $user */
                            $user = $em->getRepository('AppBundle:User')->findOneByOrganizationAndFullName($organization, $tutor, new \DateTime());
                            if ($user && !in_array($user, $teacherCollection[$subjectName]['teachers'])) {
                                $teacherCollection[$subjectName]['teachers'][] = $user;
                                $teacherCache[$tutor] = $user;
                            }
                        }
                    }
                }
            }

            if ($addNewTeachers || $removeExistingTeachers) {
                foreach ($teacherCollection as $data) {
                    /** @var Element $subject */
                    $subject = $data['subject'];
                    $oldRoles = $subject->getRoles();
                    $oldTeachers = [];
                    $oldTeachersReferences = [];
                    foreach ($oldRoles as $role) {
                        if ($role->getProfile() === $teacherProfile) {
                            $oldTeachers[] = $role->getUser();
                            $oldTeachersReferences[$role->getUser()->getId()] = $role;
                        }
                    }

                    $insert = array_diff($data['teachers'], $oldTeachers);
                    $delete = array_diff($oldTeachers, $data['teachers']);

                    if ($removeExistingTeachers) {
                        /** @var User $teacher */
                        foreach ($delete as $teacher) {
                            $em->remove($oldTeachersReferences[$teacher->getId()]);
                        }
                    }

                    if ($addNewTeachers) {
                        foreach ($insert as $teacher) {
                            $role = new Role();
                            $role
                                ->setElement($subject)
                                ->setProfile($teacherProfile)
                                ->setUser($teacher);
                            $em->persist($role);
                            $subject->addRole($role);
                        }
                    }
                }
            }
            $em->flush();
        } catch (Exception $e) {
            return null;
        }

        return [
            'new_count' => $newCount,
            'existing_count' => $existingCount,
            'collection' => $collection
        ];
    }
}
