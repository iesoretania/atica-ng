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

namespace AppBundle\Form\Type\Documentation;

use AppBundle\Entity\Documentation\Entry;
use AppBundle\Entity\Element;
use AppBundle\Service\UserExtensionService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class EntryType extends AbstractType
{
    private $userExtensionService;
    private $translator;

    public function __construct(UserExtensionService $userExtensionService, TranslatorInterface $translator)
    {
        $this->userExtensionService = $userExtensionService;
        $this->translator = $translator;
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isUserLocalAdministrator = $this->userExtensionService->isUserLocalAdministrator();
        $isManager = $isUserLocalAdministrator || $options['is_manager'];
        $isOwner = $isManager || $options['is_owner'];

        $builder
            ->add('createdAt', DateTimeType::class, [
                'label' => 'form.created_at',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'disabled' => !$isUserLocalAdministrator
            ])
            ->add('updatedAt', DateTimeType::class, [
                'label' => 'form.updated_at',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'disabled' => !$isUserLocalAdministrator
            ])
            ->add('name', null, [
                'label' => 'form.name',
                'disabled' => $options['name_locked'] || !$isOwner
            ])
            ->add('description', TextareaType::class, [
                'label' => 'form.description',
                'required' => !$isOwner
            ])
            ->add('element', EntityType::class, [
                'label' => 'form.upload_profile',
                'required' => false,
                'class' => Element::class,
                'choice_label' => function(Element $element) {
                    return $element->getFullProfileName().($element->isDeleted() ? ' '.$this->translator->trans('state.disabled', [], 'general') : '');
                },
                'choices' => $options['upload_profiles'],
                'disabled' => !$isUserLocalAdministrator
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Entry::class,
            'translation_domain' => 'documentation',
            'is_manager' => false,
            'is_owner' => false,
            'name_locked' => false,
            'upload_profiles' => []
        ]);
    }
}
