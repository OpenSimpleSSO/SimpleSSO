<?php

namespace App\Form\PasswordRecovery;

use App\Model\Data\PasswordRecovery\PasswordRecovery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordRecoveryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class)
            ->add('passwordRepeat', PasswordType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', PasswordRecovery::class);
        $resolver->setDefault('label_format', 'userManagement.register.%name%');
    }
}
