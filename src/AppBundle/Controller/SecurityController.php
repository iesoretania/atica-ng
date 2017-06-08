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

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/entrar", name="login", methods={"GET"})
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // obtener el error de entrada, si existe alguno
        $error = $authenticationUtils->getLastAuthenticationError();

        // último nombre de usuario introducido
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            array(
                'last_username' => $lastUsername,
                'login_error' => $error,
            )
        );
    }

    /**
     * @Route("/comprobar", name="login_check", methods={"POST"})
     * @Route("/salir", name="logout", methods={"GET"})
     */
    public function logInOutCheckAction()
    {
    }

    /**
     * @Route("/restablecer", name="login_password_reset", methods={"GET", "POST"})
     */
    public function passwordResetRequestAction(Request $request)
    {
    }

    /**
     * @Route("/restablecer/{userId}/{token}", name="login_password_reset_do", methods={"GET", "POST"})
     */
    public function passwordResetAction(Request $request, $userId, $token)
    {
    }
}
