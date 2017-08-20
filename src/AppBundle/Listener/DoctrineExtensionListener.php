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

namespace AppBundle\Listener;

use Gedmo\Blameable\BlameableListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DoctrineExtensionListener implements EventSubscriberInterface
{
    private $tokenStorage;
    private $authorizationChecker;
    private $blameableListener;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, BlameableListener $blameableListener)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->blameableListener = $blameableListener;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $tokenStorage = $this->tokenStorage->getToken();
        if (null !== $tokenStorage && $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->blameableListener->setUserValue($tokenStorage->getUser());
        }
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }
}