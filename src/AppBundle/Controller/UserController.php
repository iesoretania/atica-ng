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

use AppBundle\Form\Type\OwnUserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/perfil", name="user_profile_form", methods={"GET", "POST"})
     */
    public function userProfileFormAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(OwnUserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $translator = $this->get('translator');
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $translator->trans('message.saved', [], 'user'));
                return $this->redirectToRoute('frontpage');
            }
            catch (Exception $e) {
                $this->addFlash('error', $translator->trans('message.error', [], 'user'));
            }
        }

        return $this->render('user/profile_form.html.twig', [
            'title' => $this->get('translator')->trans('user.profile', [], 'layout'),
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}