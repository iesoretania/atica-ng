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
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/usuarios")
 */
class UserController extends Controller
{
    /**
     * @Route("/nuevo", name="admin_user_form_new")
     * @Route("/{id}", name="admin_user_form_edit", requirements={"id" = "\d+"})
     */
    public function indexAction(User $user = null, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if (null === $user) {
            $user = new User();
            $em->persist($user);
        }

        $form = $this->createForm(UserType::class, $user, [
            'own' => $this->getUser()->getId() === $user->getId(),
            'admin' => $this->getUser()->isGlobalAdministrator(),
            'new' => $user->getId() === null
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si es solicitado, cambiar la contraseña
            $passwordSubmit = $form->get('changePassword');
            if (($passwordSubmit instanceof SubmitButton) && $passwordSubmit->isClicked()) {
                $user->setPassword($this->container->get('security.password_encoder')
                    ->encodePassword($user, $form->get('newPassword')->get('first')->getData()));
                $message = $this->get('translator')->trans('message.password_changed', [], 'user');
            } else {
                $message = $this->get('translator')->trans('message.saved', [], 'user');
            }

            try {
                $em->flush();
                $this->addFlash('success', $message);
                return $this->redirectToRoute('admin_user_list');
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('message.error', [], 'user'));
            }
        }

        $title = $this->get('translator')->trans($user->getId() ? 'title.edit' : 'title.new', [], 'user');

        $breadcrumb = [];

        if ($user->getId()) {
            $breadcrumb[] = ['fixed' => (string) $user];
        } else {
            $breadcrumb[] = ['fixed' => $this->get('translator')->trans('title.new', [], 'user')];
        }

        return $this->render('user/profile_form.html.twig', [
            'menu_path' => 'admin_user_list',
            'breadcrumb' => $breadcrumb,
            'title' => $title,
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/listar/{page}", name="admin_user_list", requirements={"page" = "\d+"}, defaults={"page" = "1"})
     */
    public function listAction($page, Request $request)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();

        $queryBuilder
            ->select('u')
            ->from('AppBundle:User', 'u');

        $q = $request->get('q', null);
        if ($q) {
            $queryBuilder
                ->where('u.id = :q')
                ->orWhere('u.userName LIKE :tq')
                ->orWhere('u.firstName LIKE :tq')
                ->orWhere('u.lastName LIKE :tq')
                ->orWhere('u.emailAddress LIKE :tq')
                ->setParameter('tq', '%'.$q.'%')
                ->setParameter('q', $q);
        }

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pager = new Pagerfanta($adapter);
        $pager
            ->setMaxPerPage($this->getParameter('page.size'))
            ->setCurrentPage($page);

        $title = $this->get('translator')->trans('title.list', [], 'user');

        return $this->render('user/list.html.twig', [
            'title' => $title,
            'users' => $pager->getIterator(),
            'pager' => $pager,
            'q' => $q,
            'domain' => 'user'
        ]);
    }
}
