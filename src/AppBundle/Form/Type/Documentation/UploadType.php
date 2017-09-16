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
use AppBundle\Entity\Element;
use AppBundle\Form\Model\DocumentUpload;
use AppBundle\Service\UserExtensionService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class UploadType extends AbstractType
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
        if ($this->userExtensionService->isUserLocalAdministrator()) {
            $builder
                ->add('createDate', DateTimeType::class, [
                    'label' => 'form.create_date',
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'with_seconds' => false,
                    'required' => false
                ]);
        }
        $builder
            ->add('title', null, [
                'label' => 'form.title'
            ])
            ->add('file', null, [
                'label' => 'form.file',
                'required' => true
            ])
            ->add('uploadProfile', EntityType::class, [
                'label' => 'form.upload_profile',
                'required' => true,
                'class' => Element::class,
                'choice_label' => function (Element $element) {
                    return $element->getFullProfileName() . ($element->isDeleted() ? ' ' . $this->translator->trans('state.disabled', [], 'general') : '');
                },
                'choices' => $options['upload_profiles']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'form.description',
                'required' => false,
                'attr' => ['rows' => 4]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DocumentUpload::class,
            'translation_domain' => 'upload',
            'upload_profiles' => []
        ]);
    }
}
