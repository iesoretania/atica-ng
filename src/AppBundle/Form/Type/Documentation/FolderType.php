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

use AppBundle\Entity\Documentation\Folder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FolderType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'form.name'
            ])
            ->add('versionShown', ChoiceType::class, [
                'label' => 'form.version_shown',
                'required' => true,
                'expanded' => true,
                'choices' => [
                    'form.version_shown.yes' => true,
                    'form.version_shown.no' => false
                ]
            ])
            ->add('documentFlow', ChoiceType::class, [
                'label' => 'form.document_flow',
                'required' => true,
                'expanded' => true,
                'choices' => [
                    'form.document_flow.yes' => true,
                    'form.document_flow.no' => false
                ]
            ])
            ->add('groupBy', ChoiceType::class, [
                'label' => 'form.group_by',
                'required' => true,
                'expanded' => true,
                'choices' => [
                    'form.group_by.none' => Folder::GROUP_BY_NONE,
                    'form.group_by.profile' => Folder::GROUP_BY_PROFILE,
                    'form.group_by.user' => Folder::GROUP_BY_USER
                ]
            ])
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
            'data_class' => Folder::class,
            'translation_domain' => 'documentation',
            'new' => false
        ]);
    }
}
