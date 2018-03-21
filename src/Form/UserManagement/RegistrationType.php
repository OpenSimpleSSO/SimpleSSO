<?php

namespace App\Form\UserManagement;

use App\Model\Data\UserManagement\Registration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('emailAddress', EmailType::class)
            ->add('organization', TextType::class)
            ->add('password', PasswordType::class)
            ->add('passwordRepeat', PasswordType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Registration::class);
        $resolver->setDefault('label_format', 'userManagement.registration.%name%');
    }
}
