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

use AppBundle\Entity\Element;
use AppBundle\Entity\Profile;
use AppBundle\Entity\Reference;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElementType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'form.name'
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($options) {
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
            $form
                ->add('profile', EntityType::class, [
                    'label' => 'form.profile',
                    'class' => Profile::class,
                    'required' => false,
                    'placeholder' => 'form.none',
                    'disabled' => $data->getCode() != ''
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'form.description',
                    'required' => false
                ]);

            // referencias
            /** @var EntityManager $em */
            $em = $options['entity_manager'];

            $references = $data->getPathReferences();

            /** @var Reference $reference */
            foreach($references as $reference) {
                $items = $em->getRepository('AppBundle:Element')->getChildrenQueryBuilder($reference->getTarget())
                    ->andWhere('node.folder = false')
                    ->getQuery()
                    ->getResult();

                $form
                    ->add('reference' . $reference->getTarget()->getId(), ChoiceType::class, [
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
                        'placeholder' => $options['reference_placeholder']
                ]);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Element::class,
            'translation_domain' => 'element',
            'new' => false,
            'entity_manager' => null,
            'reference_placeholder' => false
        ]);
    }
}
