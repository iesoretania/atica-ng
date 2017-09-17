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

use AppBundle\Entity\Documentation\Entry;
use AppBundle\Entity\User;
use AppBundle\Service\UserExtensionService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EntryVoter extends Voter
{
    const MANAGE = 'ENTRY_MANAGE';
    const ACCESS = 'ENTRY_ACCESS';
    const APPROVE = 'ENTRY_APPROVE';
    const REVIEW = 'ENTRY_REVIEW';
    const REQUEST_CHANGES = 'ENTRY_REQUEST_CHANGES';

    private $extensionService;
    private $accessDecisionManager;

    public function __construct(UserExtensionService $extensionService, AccessDecisionManagerInterface $accessDecisionManager) {
        $this->extensionService = $extensionService;
        $this->accessDecisionManager = $accessDecisionManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!$subject instanceof Entry) {
            return false;
        }

        if (!in_array($attribute, [self::MANAGE, self::ACCESS, self::APPROVE, self::REVIEW, self::REQUEST_CHANGES], true)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$subject instanceof Entry) {
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
        if ($organization !== $subject->getFolder()->getOrganization()) {
            return true;
        }

        // si es administrador de la organización, permitir siempre
        if ($this->extensionService->isUserLocalAdministrator()) {
            return true;
        }

        $table = [
            self::ACCESS => FolderVoter::ACCESS,
            self::APPROVE => FolderVoter::APPROVE,
            self::REVIEW => FolderVoter::REVIEW,
            self::REQUEST_CHANGES => FolderVoter::REQUEST_CHANGES,
        ];

        // todos los permisos salvo el de gestión se admiten si se tiene el mismo permiso para la carpeta
        if (isset($table[$attribute])) {
            return $this->accessDecisionManager->decide($token, [$table[$attribute]], $subject->getFolder());
        }

        // se permite la gestión si es el creador del documento original y no tiene revisión activa
        // o si es el creador de la revisión activa
        if (self::MANAGE === $attribute) {
            return (!$subject->getCurrentVersion() && $subject->getCreatedBy() === $user) ||
                ($subject->getCurrentVersion() && $subject->getCurrentVersion()->getCreatedBy() === $user);
        }

        // denegar en otro caso
        return false;
    }
}
