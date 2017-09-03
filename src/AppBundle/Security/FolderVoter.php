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

namespace AppBundle\Security;

use AppBundle\Entity\Documentation\Folder;
use AppBundle\Entity\Documentation\FolderPermission;
use AppBundle\Entity\User;
use AppBundle\Service\UserExtensionService;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FolderVoter extends Voter
{
    const MANAGE = 'FOLDER_MANAGE';
    const ACCESS = 'FOLDER_ACCESS';
    const UPLOAD = 'FOLDER_UPLOAD';
    const APPROVE = 'FOLDER_APPROVE';
    const REVIEW = 'FOLDER_REVIEW';
    const REQUEST_CHANGES = 'FOLDER_REQUEST_CHANGES';

    private $extensionService;
    private $managerRegistry;

    public function __construct(UserExtensionService $extensionService, ManagerRegistry $managerRegistry) {
        $this->extensionService = $extensionService;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!$subject instanceof Folder) {
            return false;
        }

        if (!in_array($attribute, [self::MANAGE, self::ACCESS, self::UPLOAD, self::APPROVE, self::REVIEW, self::REQUEST_CHANGES], true)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$subject instanceof Folder) {
            return false;
        }

        // los administradores globales siempre tienen permiso
        if ($this->extensionService->isUserGlobalAdministrator()) {
            return true;
        }

        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            // si el usuario no ha entrado, denegar
            return false;
        }

        $organization = $this->extensionService->getCurrentOrganization();

        // si la carpeta no pertence a la organización actual, denegar
        if ($organization !== $subject->getOrganization()) {
            return true;
        }

        // si es administrador de la organización, permitir siempre
        if ($this->extensionService->isUserLocalAdministrator()) {
            return true;
        }

        // comprobar los permisos de la carpeta
        $table = [
            self::MANAGE => FolderPermission::PERMISSION_MANAGE,
            self::ACCESS => FolderPermission::PERMISSION_VISIBLE,
            self::UPLOAD => FolderPermission::PERMISSION_UPLOAD,
            self::APPROVE => FolderPermission::PERMISSION_APPROVE,
            self::REVIEW => FolderPermission::PERMISSION_REVIEW,
            self::REQUEST_CHANGES => FolderPermission::PERMISSION_REQUEST_CHANGES,
        ];

        if (!isset($table[$attribute])) {
            return false;
        }

        $folderProfiles = $this->managerRegistry->getRepository('AppBundle:Element')
            ->findAllProfilesByFolderPermission($subject, $table[$attribute]);

        // caso especial: si una carpeta no tiene perfiles en ACCESS es porque se permite a la totalidad de usuarios
        if (self::ACCESS === $attribute && empty($folderProfiles)) {
            return true;
        }

        // si el usuario tiene al menos uno de los perfiles solicitados, permitir acceso
        $granted = $this->managerRegistry->getRepository('AppBundle:Role')->countByUserAndElements($user, $folderProfiles) > 0;

        return $granted;
    }
}
