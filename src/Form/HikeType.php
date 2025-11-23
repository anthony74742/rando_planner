<?php

namespace App\Form;

use App\Entity\Hike;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class HikeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('location')
            ->add('distance', NumberType::class, [
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'step' => '0.1',
                ],
            ])
            ->add('duration', NumberType::class, [
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'step' => '0.1',
                ],
            ])
            ->add('difficulty', ChoiceType::class, [
                'choices' => [
                    'Facile' => 'facile',
                    'Moyen' => 'moyen',
                    'Difficile' => 'difficile'
                ]
            ])
            ->add('isPublic')
            ->add('gpxFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'label' => 'Trace GPX',
                'attr' => [
                    'accept' => '.gpx',
                ],
                'help' => 'Ajoutez un fichier .gpx pour pré-remplir la distance, la durée et afficher l’itinéraire.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hike::class,
        ]);
    }
}
