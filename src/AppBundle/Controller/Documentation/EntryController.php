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
use AppBundle\Entity\User;
use AppBundle\Entity\Documentation\Version;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
}
