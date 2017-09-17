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

use AppBundle\Entity\Documentation\Entry;
use AppBundle\Entity\Documentation\Folder;
use AppBundle\Entity\Documentation\FolderPermission;
use AppBundle\Entity\Documentation\FolderRepository;
use AppBundle\Entity\Documentation\History;
use AppBundle\Entity\Documentation\Version;
use AppBundle\Entity\ElementRepository;
use AppBundle\Entity\Organization;
use AppBundle\Form\Model\DocumentUpload;
use AppBundle\Form\Type\Documentation\FolderType;
use AppBundle\Form\Type\Documentation\UploadType;
use AppBundle\Security\OrganizationVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/documentos")
 */
class FolderController extends Controller
{
    /**
     * @Route("/carpeta/{id}/nueva", name="documentation_folder_new", methods={"GET", "POST"})
     * @Route("/carpeta/{id}", name="documentation_folder_form", requirements={"id" = "\d+"}, methods={"GET", "POST"})
     * @Security("is_granted('FOLDER_MANAGE', folder)")
     */
    public function folderFormAction(Folder $folder = null, Request $request)
    {
        $organization = $this->get('AppBundle\Service\UserExtensionService')->getCurrentOrganization();
        $this->denyAccessUnlessGranted(OrganizationVoter::MANAGE, $organization);

        $em = $this->getDoctrine()->getManager();
        $new = $request->get('_route') === 'documentation_folder_new';

        $sourceFolder = $folder;

        if ($new) {
            $newFolder = new Folder();
            $newFolder
                ->setOrganization($organization)
                ->setParent($folder);
            $folder = $newFolder;
            $em->persist($folder);
        } else {
            if (null === $sourceFolder->getParent()) {
                throw $this->createAccessDeniedException();
            }
        }
        $breadcrumb = $sourceFolder->getParent() ? $this->generateBreadcrumb($sourceFolder, false) : [];

        if ($request->request->get('folder')) {
            $folder->setType($request->request->get('folder')['type']);
        }
        $form = $this->createForm(FolderType::class, $folder, [
            'new' => $new,
            'allow_extra_fields' => !$request->request->has('submit')
        ]);

        $this->setFolderRolesInForm($folder, $form);
        $form->handleRequest($request);
        $breadcrumb[] = ['fixed' => $this->get('translator')->trans($new ? 'title.folder.new' : 'title.folder.edit', [], 'documentation')];

        if ($form->isSubmitted() && $form->isValid() && $request->request->has('submit')) {
            try {
                $this->updateFolderRolesFromForm($folder, $em, $form);
                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('message.folder.saved', [], 'documentation'));
                return $this->redirectToRoute('documentation', ['id' => $sourceFolder->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('message.folder.save_error', [], 'documentation'));
            }
        }

        return $this->render('documentation/folder_form.html.twig', [
            'menu_path' => 'documentation',
            'breadcrumb' => $breadcrumb,
            'title' => $this->get('translator')->trans($new ? 'title.folder.new' : 'title.folder.edit', [], 'documentation'),
            'form' => $form->createView(),
            'folder' => $folder
        ]);
    }

    /**
     * @param Folder $folder
     * @param Form $form
     */
    private function setFolderRolesInForm(Folder $folder = null, Form $form)
    {
        if (null === $folder) {
            return;
        }

        $permissions = $folder->getPermissions();

        $permissionTypes = [
            'access' => FolderPermission::PERMISSION_VISIBLE,
            'manager' => FolderPermission::PERMISSION_MANAGE,
            'upload' => FolderPermission::PERMISSION_UPLOAD,
            'review' => FolderPermission::PERMISSION_REVIEW,
            'approve' => FolderPermission::PERMISSION_APPROVE
        ];

        foreach ($permissionTypes as $name => $type) {
            if ($form->has('profiles_'.$name)) {
                $data = [];

                /** @var FolderPermission $permission */
                foreach ($permissions as $permission) {
                    if ($permission->getPermission() === $type) {
                        $data[] = $permission->getElement();
                    }
                }

                if (!empty($data)) {
                    $form->get('profiles_' . $name)->setData($data);
                }
            }
        }
    }

    /**
     * @param Folder $folder
     * @param EntityManager $em
     * @param Form $form
     */
    private function updateFolderRolesFromForm($folder, EntityManager $em, $form)
    {
        $oldPermissions = $folder->getPermissions();

        $permissionTypes = [
            'access' => FolderPermission::PERMISSION_VISIBLE,
            'manager' => FolderPermission::PERMISSION_MANAGE,
            'upload' => FolderPermission::PERMISSION_UPLOAD,
            'review' => FolderPermission::PERMISSION_REVIEW,
            'approve' => FolderPermission::PERMISSION_APPROVE
        ];

        foreach ($permissionTypes as $name => $type) {

            $data = $form->has('profiles_' . $name) ? $form->get('profiles_' . $name)->getData() : [];
            if (!$data instanceof ArrayCollection) {
                $data = new ArrayCollection($data);
            }

            /** @var FolderPermission $permission */
            foreach ($oldPermissions as $permission) {
                if ($permission->getPermission() === $type) {
                    if (!$data->contains($permission->getElement())) {
                        $em->remove($permission);
                    } else {
                        $data->removeElement($permission->getElement());
                    }
                }
            }

            foreach ($data as $datum) {
                $permission = new FolderPermission();
                $permission
                    ->setFolder($folder)
                    ->setPermission($type)
                    ->setElement($datum);
                $em->persist($permission);
            }
        }
        $em->flush();
    }

    /**
     * @Route("/operacion/{id}", name="documentation_operation", requirements={"id" = "\d+"}, methods={"POST"})
     * @Security("is_granted('FOLDER_MANAGE', folder)")
     */
    public function operationAction($id, Request $request)
    {
        $organization = $this->get('AppBundle\Service\UserExtensionService')->getCurrentOrganization();

        $folder = $this->getFolder($organization, $id);

        if (null === $folder || $folder->getOrganization() !== $organization) {
            throw $this->createNotFoundException();
        }
        $ok = false;
        $em = $this->getDoctrine()->getManager();
        foreach (['up', 'down'] as $op) {
            if ($request->get($op)) {
                $method = 'move'.ucfirst($op);
                $em->getRepository('AppBundle:Documentation\Folder')->$method($folder);
                $ok = true;
            }
        }
        if ($ok) {
            $em->flush();
        }
        return $this->redirectToRoute('documentation', ['id' => $folder->getId()]);
    }

    /**
     * @Route("/{id}/{page}", name="documentation", requirements={"page" = "\d+", "id" = "\d+"}, defaults={"page" = "1", "folder" = null}, methods={"GET"})
     */
    public function browseAction($page, $id = null, Request $request)
    {
        $organization = $this->get('AppBundle\Service\UserExtensionService')->getCurrentOrganization();

        $q = $request->get('q', null);

        $folder = (null === $id) ? $this->getRootFolder($organization) : $this->getFolder($organization, $id);

        if (null === $folder) {
            throw $this->createNotFoundException();
        }
        $this->denyAccessUnlessGranted('FOLDER_ACCESS', $folder);

        $pager = $this->getFolderEntriesPager($page, $folder, $q);

        $breadcrumb = $this->generateBreadcrumb($folder);

        return $this->render('documentation/list.html.twig', [
            'breadcrumb' => $breadcrumb,
            'pager' => $pager,
            'current' => $folder,
            'permissions' => ['is_folder_manager' => $this->isGranted('FOLDER_MANAGE', $folder), 'is_organization_manager' => $this->isGranted('ORGANIZATION_MANAGE', $organization)],
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
        $folders = $this->getDoctrine()->getManager()->getRepository('AppBundle:Documentation\Folder')->getChildren($folder, false, false, 'ASC', true);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $entriesRepository->createQueryBuilder('e')
            ->andWhere('e.folder IN (:folders)')
            ->setParameter('folders', $folders)
            ->join('e.folder', 'f')
            ->addOrderBy('f.left')
            ->addOrderBy('e.position')
            ->addSelect('v')
            ->leftJoin('e.currentVersion', 'v');

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

        $organization = $folder->getOrganization();
        $disabled = $this->isGranted('ORGANIZATION_MANAGE', $organization) ? [] : array_map(function (Folder $f) {
            return $f->getId();
        }, $folderRepository->getAccessDeniedFoldersForUserAndOrganizationArray($this->getUser(), $organization));

        list($tree) = $this->processChildren($children, $current ? $current->getId() : null, $disabled);

        return $tree;
    }

    /**
     * Convert children array into a treeview array
     *
     * @param array $children
     * @param integer $currentId
     * @param array $disabledId
     * @return mixed
     */
    private function processChildren(array $children, $currentId = null, $disabledId = [])
    {
        $result = [];
        $selected = false;
        foreach ($children as $child) {
            $item = [];
            $item['text'] = $child['name'];

            $disabled = in_array($child['id'], $disabledId,false);
            if ($disabled) {
                $item['state'] = ['disabled' => true];
            }
            if ($currentId === $child['id']) {
                $item['state'] = ['selected' => true, 'expanded' => true];
                $selected = true;
            }
            if (!$disabled && count($child['__children']) > 0) {
                list($item['nodes'], $selected) = $this->processChildren($child['__children'], $currentId, $disabledId);
            } else {
                $item['icon'] = 'fa fa-folder';
            }
            if ($selected) {
                if (!isset($item['state'])) {
                    $item['state'] = [];
                }
                $item['state']['expanded'] = true;
            }
            $item['href'] = $this->generateUrl('documentation', ['id' => $child['id']]);
            $result[] = $item;
        }

        return [$result, $selected];
    }

    /**
     * @Route("/carpeta/{id}/subir", name="documentation_folder_upload", methods={"GET", "POST"})
     * @Security("is_granted('FOLDER_UPLOAD', folder) and folder.getType() != constant('AppBundle\\Entity\\Documentation\\Folder::TYPE_TASKS')")
     */
    public function uploadFormAction(Request $request, Folder $folder)
    {
        $breadcrumb = $this->generateBreadcrumb($folder, false);

        $title = $this->get('translator')->trans('title.entry.new', [], 'documentation');
        $breadcrumb[] = ['fixed' => $title];

        $upload = new DocumentUpload();

        if ($this->isGranted('FOLDER_MANAGE', $folder)) {
            $profiles = $this->getDoctrine()->getManager()->getRepository('AppBundle:Element')->findAllProfilesByFolderPermission($folder, FolderPermission::PERMISSION_UPLOAD);
        } else {
            $profiles = $this->getDoctrine()->getManager()->getRepository('AppBundle:Element')->findAllProfilesByFolderPermissionAndUser($folder, FolderPermission::PERMISSION_UPLOAD, $this->getUser());
        }
        $form = $this->createForm(UploadType::class, $upload, ['upload_profiles' => $profiles]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->processFileUpload($folder, $upload)) {
                $this->addFlash('success', $this->get('translator')->trans('message.upload.save_ok', [], 'upload'));
                return $this->redirectToRoute('documentation', ['id' => $folder->getId()]);
            }
            $this->addFlash('error', $this->get('translator')->trans('message.upload.save_error', [], 'upload'));
        }

        return $this->render('documentation/folder_upload.html.twig', [
            'menu_path' => 'documentation',
            'title' => $title,
            'breadcrumb' => $breadcrumb,
            'folder' => $folder,
            'form' => $form->createView()
        ]);
    }

    private function processFileUpload(Folder $folder, DocumentUpload $upload)
    {
        $em = $this->getDoctrine()->getManager();
        $processedFileName = null;
        $filesystem = $this->get('entries_filesystem');
        try {
            /** @var UploadedFile $file */
            $file = $upload->getFile();
            $fileName = hash_file('sha256', $file->getRealPath());
            $fileName = substr($fileName, 0, 2).'/'.substr($fileName, 2, 2).'/'.$fileName;
            if (!$filesystem->has($fileName)) {
                $filesystem->write($fileName, file_get_contents($file->getRealPath()));
            }
            $processedFileName = $fileName;

            $entry = new Entry();
            $entry
                ->setName($upload->getTitle() ?: $file->getClientOriginalName())
                ->setFolder($folder)
                ->setElement($upload->getUploadProfile())
                ->setDescription($upload->getDescription());

            if ($upload->getCreateDate()) {
                $entry->setCreatedAt($upload->getCreateDate());
            }

            $em->persist($entry);

            $version = new Version();
            $version
                ->setEntry($entry)
                ->setFile($fileName)
                ->setState(Version::STATUS_APPROVED)
                ->setVersionNr($upload->getVersion());

            $entry->setCurrentVersion($version);

            $em->persist($version);

            $history = new History();
            $history
                ->setEntry($entry)
                ->setVersion($upload->getVersion())
                ->setCreatedBy($this->getUser())
                ->setEvent(History::LOG_CREATE);

            $em->persist($history);

            $em->flush();

            return true;
        } catch (\Exception $e) {
        }

        if ($processedFileName) {
            // ha ocurrido un error pero el fichero se había almacenado, borrarlo si no se estaba usando
            if (0 == (int) $em->getRepository('AppBundle:Documentation\Version')->countByFile($processedFileName)) {
                $filesystem->delete($processedFileName);
            }
        }

        return false;
    }
}
