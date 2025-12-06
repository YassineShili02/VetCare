<?php

namespace App\Form;

use App\Entity\DossierMedical;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Animal;

class DossierMedicalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('animal', EntityType::class, [
                'class' => Animal::class,
                'choice_label' => 'nom',
                'label' => 'Sélectionner un animal',
                'placeholder' => 'Sélectionner un animal',
            ])
            ->add('poids', TextType::class, ['label' => 'Poids actuel'])
            ->add('etat', TextType::class, ['label' => 'État général'])
            ->add('notes_Veterinaire', TextareaType::class, ['label' => 'Notes vétérinaire', 'required' => false])
            ->add('allergies', TextareaType::class, ['label' => 'Allergies', 'required' => false])
            ->add('vaccinations', TextareaType::class, ['label' => 'Vaccinations', 'required' => false])
            ->add('antecedents_medicaux', TextareaType::class, ['label' => 'Antécédents médicaux', 'required' => false])
            ->add('images', FileType::class, [
                'label' => 'Images médicales',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DossierMedical::class,
        ]);
    }
}
