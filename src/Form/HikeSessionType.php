<?php

namespace App\Form;

use App\Entity\Hike;
use App\Entity\HikeSession;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HikeSessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('notes')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('hike', EntityType::class, [
                'class' => Hike::class,
                'choice_label' => 'id',
            ])
            ->add('creator', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HikeSession::class,
        ]);
    }
}
