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

namespace AppBundle\Form\Type;

use AppBundle\Entity\Actor;
use AppBundle\Entity\Element;
use AppBundle\Entity\Profile;
use AppBundle\Entity\Reference;
use AppBundle\Service\UserExtensionService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class ElementType extends AbstractType
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TranslatorInterface */
    private $translator;

    /** @var UserExtensionService */
    private $userExtensionService;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator, UserExtensionService $userExtensionService)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->userExtensionService = $userExtensionService;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'form.name'
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'buildDynamicForm']);
    }

    public function buildDynamicForm(FormEvent $event) {
        $form = $event->getForm();
        /** @var Element $data */
        $data = $event->getData();

        if ($data->isFolder()) {
            $form
                ->add('included', ChoiceType::class, [
                    'label' => 'form.included',
                    'expanded' => true,
                    'choices' => [
                        'form.included_false' => false,
                        'form.included_true' => true
                    ]
                ]);
        }

        if (!$data->getCode()) {
            $form
                ->add('profile', EntityType::class, [
                    'label' => 'form.profile',
                    'class' => Profile::class,
                    'required' => false,
                    'placeholder' => 'form.none',
                    'query_builder' => function (EntityRepository $entityRepository) use ($data) {
                        return $entityRepository->createQueryBuilder('p')
                            ->join('p.element', 'e')
                            ->where('p.organization = :organization')
                            ->andWhere('e = :element')
                            ->setParameter('organization', $data->getOrganization())
                            ->setParameter('element', $data)
                            ->orderBy('p.nameNeutral');
                    }
                ]);
        }

        // referencias
        $references = $data->getPathReferences();

        /** @var Reference $reference */
        foreach ($references as $reference) {
            $items = $this->entityManager->getRepository('AppBundle:Element')->getChildrenQueryBuilder($reference->getTarget())
                ->andWhere('node.folder = false')
                ->getQuery()
                ->getResult();

            $form
                ->add('reference'.$reference->getTarget()->getId(), ChoiceType::class, [
                    'label' => $reference->getTarget()->getName(),
                    'mapped' => false,
                    'translation_domain' => false,
                    'choice_translation_domain' => false,
                    'required' => $reference->isMandatory(),
                    'multiple' => $reference->isMultiple(),
                    'expanded' => false,
                    'choices' => $items,
                    'choice_value' => 'id',
                    'choice_label' => 'name',
                    'placeholder' => $this->translator->trans('form.none', [], 'element')
                ]);
        }


        // actores
        $actors = $data->getPathActors();

        /** @var Actor $actor */
        foreach ($actors as $actor) {
            $items = $this->entityManager->getRepository('AppBundle:User')->findByOrganizationAndDate($this->userExtensionService->getCurrentOrganization());
            $form
                ->add('role'.$actor->getRole(), ChoiceType::class, [
                    'label' => 'role.'.$actor->getRole(),
                    'mapped' => false,
                    'translation_domain' => 'core',
                    'choice_translation_domain' => false,
                    'required' => $actor->isMandatory(),
                    'multiple' => $actor->isMultiple(),
                    'expanded' => false,
                    'choices' => $items,
                    'choice_value' => 'id',
                    'choice_label' => 'fullName',
                    'placeholder' => $this->translator->trans('form.none', [], 'element')
                ]);
        };

        $form
            ->add('description', TextareaType::class, [
                'label' => 'form.description',
                'required' => false
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Element::class,
            'translation_domain' => 'element'
        ]);
    }
}
