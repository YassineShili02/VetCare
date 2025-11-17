<?php

namespace App\Form;

use App\Entity\DossierMedical;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DossierMedicalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_dossier')
            ->add('numero_dossier')
            ->add('date_creation')
            ->add('poids')
            ->add('etat')
            ->add('images')
            ->add('notes_Veterinaire')
            ->add('allergies')
            ->add('vaccinations')
            ->add('antecedents_medicaux')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DossierMedical::class,
        ]);
    }
}
