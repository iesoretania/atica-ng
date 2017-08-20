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

use AppBundle\Entity\User;
use AppBundle\Form\Type\UserType;
use AppBundle\Service\MailerService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PersonalDataController extends Controller
{
    /**
     * @Route("/datos", name="personal_data", methods={"GET", "POST"})
     */
    public function userProfileFormAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user, [
            'own' => true,
            'admin' => $user->isGlobalAdministrator()
        ]);

        $form->get('newEmailAddress')->setData($user->getEmailAddress());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $translator = $this->get('translator');

            $newEmail = $form->get('newEmailAddress')->getData();

            // comprobar si ha cambiado el correo electrónico
            if ($user->getEmailAddress() !== $newEmail) {
                $this->requestEmailAddressChange($user, $newEmail);
            }

            // Si es solicitado, cambiar la contraseña
            $passwordSubmitted = ($form->has('changePassword') && $form->get('changePassword') instanceof SubmitButton) && $form->get('changePassword')->isClicked();
            if ($passwordSubmitted) {
                $user->setPassword($this->get('security.password_encoder')
                    ->encodePassword($user, $form->get('newPassword')->get('first')->getData()));
            }
            $message = $this->get('translator')->trans($passwordSubmitted ? 'message.password_changed' : 'message.saved', [], 'user');

            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $message);
                return $this->redirectToRoute('frontpage');
            } catch (Exception $e) {
                $this->addFlash('error', $translator->trans('message.error', [], 'user'));
            }
        }

        return $this->render('user/personal_data_form.html.twig', [
            'menu_path' => 'frontpage',
            'breadcrumb' => [
                ['caption' => 'menu.personal_data']
            ],
            'title' => $this->get('translator')->trans('user.data', [], 'layout'),
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @param $user
     * @param $newEmail
     */
    private function requestEmailAddressChange(User $user, $newEmail)
    {
        if ($user->isGlobalAdministrator()) {
            $user->setEmailAddress($newEmail);
        } else {
            $user->setTokenType($newEmail);
            // generar un nuevo token
            $token = bin2hex(random_bytes(16));
            $user->setToken($token);

            // obtener tiempo de expiración del token
            $expire = (int) $this->getParameter('password_reset.expire');

            // calcular fecha de expiración del token
            $validity = new \DateTime();
            $validity->add(new \DateInterval('PT'.$expire.'M'));
            $user->setTokenExpiration($validity);

            $old = $user->getEmailAddress();
            $user->setEmailAddress($newEmail);

            // enviar correo
            if (0 === $this->get(MailerService::class)->sendEmail([$user],
                    ['id' => 'form.change_email.email.subject', 'parameters' => []],
                    [
                        'id' => 'form.change_email.email.body',
                        'parameters' => [
                            '%name%' => $user->getFirstName(),
                            '%link%' => $this->generateUrl('email_reset_do',
                                ['userId' => $user->getId(), 'token' => $token],
                                UrlGeneratorInterface::ABSOLUTE_URL),
                            '%expiry%' => $expire
                        ]
                    ], 'security')
            ) {
                $this->addFlash('error', $this->get('translator')->trans('message.email_change.error', [], 'user'));
            } else {
                $this->addFlash('info',
                    $this->get('translator')->trans('message.email_change.info', [], 'user'));
            }

            $user->setEmailAddress($old);
        }
    }
}
