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

namespace AppBundle\Form\Type\Import;

use AppBundle\Form\Model\SubjectImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubjectType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'form.file',
                'required' => true
            ])
            ->add('addNewTeachers', ChoiceType::class, [
                'label' => 'form.add_new_teachers',
                'required' => true,
                'expanded' => true,
                'choices' => [
                    'form.add_new_teachers.no' => false,
                    'form.add_new_teachers.yes' => true
                ]
            ])
            ->add('removeExistingTeachers', ChoiceType::class, [
                'label' => 'form.remove_old_teachers',
                'required' => true,
                'expanded' => true,
                'choices' => [
                    'form.remove_old_teachers.no' => false,
                    'form.remove_old_teachers.yes' => true
                ]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SubjectImport::class,
            'translation_domain' => 'import'
        ]);
    }
}
