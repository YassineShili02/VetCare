<?php

namespace App\Form;

use App\Entity\Rendezvous;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RendezvousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', TextType::class)
            ->add('duree', IntegerType::class)
            ->add('commentaireClient', TextType::class, ['required' => false])
            ->add('notesVeterinaire', TextType::class, ['required' => false])
            ->add('confirmation', CheckboxType::class, [
                'required' => false,
                'label' => 'Confirmé ?'
            ])
            ->add('modePaiementPrevu', ChoiceType::class, [
                'choices' => [
                    'Cash' => 'cash',
                    'Carte' => 'card',
                    'Chèque' => 'cheque'
                ]
            ]);
        // createdAt et updatedAt NE DOIVENT PAS être dans le formulaire
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rendezvous::class,
        ]);
    }
}
