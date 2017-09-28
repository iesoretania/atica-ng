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

use AppBundle\Entity\Documentation\DownloadLog;
use AppBundle\Entity\Documentation\Entry;
use AppBundle\Entity\Documentation\Folder;
use AppBundle\Entity\Documentation\FolderPermission;
use AppBundle\Entity\Documentation\History;
use AppBundle\Entity\User;
use AppBundle\Entity\Documentation\Version;
use AppBundle\Form\Model\DocumentUpload;
use AppBundle\Form\Type\Documentation\EntryType;
use AppBundle\Form\Type\Documentation\UploadType;
use AppBundle\Security\EntryVoter;
use AppBundle\Security\FolderVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class EntryController extends Controller
{
    /**
     * @Route("/publico/{id}/{publicToken}", name="documentation_entry_public_download", requirements={"id" = "\d+"}, methods={"GET"})
     * @Security("entry.isPublic()")
     */
    public function publicDownloadEntryAction(Request $request, Entry $entry)
    {
        return $this->doDownloadEntry($request, $entry, null);
    }

    /**
     * @Route("/documentos/descargar/{id}", name="documentation_entry_download", requirements={"id" = "\d+"}, methods={"GET"})
     * @Security("is_granted('ENTRY_ACCESS', entry)")
     */
    public function downloadEntryAction(Request $request, Entry $entry)
    {
        return $this->doDownloadEntry($request, $entry, $this->getUser());
    }

    /**
     * @param Request $request
     * @param Entry $entry
     * @param User|null $user
     *
     * @return BinaryFileResponse
     */
    private function doDownloadEntry(Request $request, Entry $entry, User $user = null)
    {
        $version = $entry->getCurrentVersion();
        if (null === $version || null === $version->getFile()) {
            throw $this->createNotFoundException();
        }
        return $this->doDownloadVersion($request, $version, $user);
    }

    /**
     * @param Request $request
     * @param Version $version
     * @param User|null $user
     *
     * @return BinaryFileResponse
     */
    private function doDownloadVersion(Request $request, Version $version, User $user = null)
    {
        $filepath = 'gaufrette://entries/'.$version->getFile();

        $response = new BinaryFileResponse($filepath);

        $fileName = $version->getFileExtension() ? $version->getEntry()->getName().'.'.$version->getFileExtension() : $version->getEntry()->getName();

        $response
            ->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                $fileName
            );

        $em = $this->getDoctrine()->getManager();

        $log = new DownloadLog();
        $log
            ->setUser($user)
            ->setVersion($version->getVersionNr())
            ->setEntry($version->getEntry())
            ->setIpAddress($request->getClientIp());

        $em->persist($log);

        $em->flush();

        return $response;
    }


    /**
     * @Route("/carpeta/{id}/subir", name="documentation_folder_upload", methods={"GET", "POST"})
     * @Security("is_granted('FOLDER_UPLOAD', folder) and folder.getType() != constant('AppBundle\\Entity\\Documentation\\Folder::TYPE_TASKS')")
     */
    public function uploadFormAction(Request $request, Folder $folder)
    {
        $breadcrumb = FolderController::generateBreadcrumb($folder, false);

        $title = $this->get('translator')->trans('title.entry.new', [], 'documentation');
        $breadcrumb[] = ['fixed' => $title];

        $upload = new DocumentUpload();

        if ($this->isGranted(FolderVoter::MANAGE, $folder)) {
            $profiles = $this->getDoctrine()->getManager()->getRepository('AppBundle:Element')->findAllProfilesByFolderPermission($folder, FolderPermission::PERMISSION_UPLOAD, true);
        } else {
            $profiles = $this->getDoctrine()->getManager()->getRepository('AppBundle:Element')->findAllProfilesByFolderPermissionAndUser($folder, FolderPermission::PERMISSION_UPLOAD, $this->getUser(), true);
        }
        $form = $this->createForm(UploadType::class, $upload, ['upload_profiles' => $profiles]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $state = $this->getUploadStatus($request, $folder);
            if (null !== $state && $this->processFileUpload($folder, $upload, $state, $state)) {
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

    /**
     * @param Folder $folder
     * @param DocumentUpload $upload
     * @param integer $versionState
     * @param integer $entryState
     */
    private function processFileUpload(Folder $folder, DocumentUpload $upload, $versionState = Version::STATUS_APPROVED, $entryState = Entry::STATUS_APPROVED)
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

            $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $entry = new Entry();
            $entry
                ->setName($upload->getTitle() ?: $name)
                ->setFolder($folder)
                ->setState($entryState)
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
                ->setFileExtension($file->getClientOriginalExtension())
                ->setFileMimeType($file->getMimeType())
                ->setState($versionState)
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
            // seguir la ejecución si ha ocurrido una excepción por el camino
        }

        if (null !== $processedFileName) {
            // ha ocurrido un error pero el fichero se había almacenado, borrarlo si no se estaba usando
            if (0 == (int) $em->getRepository('AppBundle:Documentation\Version')->countByFile($processedFileName)) {
                $filesystem->delete($processedFileName);
            }
        }

        return false;
    }

    /**
     * @param Request $request
     * @param Folder $folder
     * @return int|null
     */
    private function getUploadStatus(Request $request, Folder $folder)
    {
        $state = null;
        switch ($folder->getType()) {
            case Folder::TYPE_NORMAL:
                $state = Version::STATUS_APPROVED;
                break;
            case Folder::TYPE_WORKFLOW:
                $state = ($request->request->has('approve') && $this->isGranted(FolderVoter::APPROVE, $folder)) ? Version::STATUS_APPROVED : Version::STATUS_DRAFT;
        }
        return $state;
    }

    /**
     * @Route("/documentos/detalle/{id}", name="documentation_entry_detail", requirements={"id" = "\d+"}, methods={"GET", "POST"})
     * @Security("is_granted('ENTRY_ACCESS', entry)")
     */
    public function detailEntryAction(Request $request, Entry $entry)
    {
        $breadcrumb = FolderController::generateBreadcrumb($entry->getFolder(), false);

        $title = $this->get('translator')->trans('title.entry.edit', [], 'documentation');
        $breadcrumb[] = ['fixed' => $entry->getName()];

        $folder = $entry->getFolder();
        $isManager = $this->isGranted(FolderVoter::MANAGE, $folder);
        if ($isManager) {
            $profiles = $this->getDoctrine()->getManager()->getRepository('AppBundle:Element')->findAllProfilesByFolderPermission($folder, FolderPermission::PERMISSION_UPLOAD, true);
        } else {
            $profiles = $this->getDoctrine()->getManager()->getRepository('AppBundle:Element')->findAllProfilesByFolderPermissionAndUser($folder, FolderPermission::PERMISSION_UPLOAD, $this->getUser(), true);
        }

        $isOwner = $this->isGranted(EntryVoter::MANAGE, $entry);
        $form = $this->createForm(EntryType::class, $entry, [
            'upload_profiles' => $profiles,
            'is_manager' => $isManager,
            'is_owner' => $isOwner,
            'name_locked' => $folder->getType() === Folder::TYPE_TASKS
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $this->get('translator')->trans('message.entry.saved', [], 'documentation'));
                return $this->redirectToRoute('documentation', ['id' => $folder->getId()]);
            }
            catch(Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('message.entry.save_error', [], 'documentation'));
            }
        }

        return $this->render('documentation/entry_detail.html.twig', [
            'menu_path' => 'documentation',
            'title' => $title,
            'breadcrumb' => $breadcrumb,
            'permissions' => [
                'is_owner' => $isOwner,
                'is_manager' => $isManager,
                'is_reviewer' => $this->isGranted(FolderVoter::REVIEW, $folder),
                'is_approver' => $this->isGranted(FolderVoter::APPROVE, $folder)
            ],
            'entry' => $entry,
            'form' => $form->createView()
        ]);
    }
}
