<?php

namespace App\Form\Admin\UserAccount;

use App\Form\UserManagement\AttributesType;
use App\Model\Data\Admin\UserAccount\ProfileEdition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileEditionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('emailAddress', EmailType::class)
            ->add('roles', TextareaType::class)
            ->add('enabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('extraData', AttributesType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ProfileEdition::class);
        $resolver->setDefault('label_format', 'attribute.userAccount.%name%');
    }
}
