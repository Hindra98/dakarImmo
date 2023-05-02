<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use function Symfony\Bridge\Twig\Extension\twig_is_selected_choice;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roles')
            ->add('password', PasswordType::class)
            ->add('nom')
            ->add('create_at')
            ->add('email', EmailType::class)
            ->add('profil', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '3072k',
                        'mimeTypes' => [
                            'image/*'
                        ],
                        'mimeTypesMessage' => 'SVP choisissez une image valide (moins de 3Mo)'
                    ])
                ],
            ])
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'H' => 'H',
                    'F' => 'F'
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('update_at')
            ->add('valider', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
