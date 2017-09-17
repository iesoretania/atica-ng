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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/documentos")
 */
class EntryController extends Controller
{
    /**
     * @Route("/descargar/{id}", name="documentation_entry_download", methods={"GET"})
     * @Security("is_granted('ENTRY_ACCESS', entry)")
     */
    public function folderFormAction(Entry $entry)
    {
        $version = $entry->getCurrentVersion();
        if (null === $version || null === $version->getFile()) {
            throw $this->createNotFoundException();
        }
        $filepath = 'gaufrette://entries/'.$version->getFile();

        $response = new BinaryFileResponse($filepath);

        $response
            ->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                $entry->getName()
            );

        $em = $this->getDoctrine()->getManager();

        $log = new DownloadLog();
        $log
            ->setUser($this->getUser())
            ->setVersion($version->getVersionNr())
            ->setEntry($entry);

        $em->persist($log);

        $em->flush();

        return $response;
    }
}
