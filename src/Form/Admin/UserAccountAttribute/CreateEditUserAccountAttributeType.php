<?php

namespace App\Form\Admin\UserAccountAttribute;

use App\Entity\UserAccountAttribute;
use App\Model\Data\Admin\UserAccountAttribute\CreateEditUserAccountAttribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateEditUserAccountAttributeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('key', TextType::class)
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'attribute.userAccountAttribute.typeValue.' . UserAccountAttribute::TYPE_BOOL     => UserAccountAttribute::TYPE_BOOL,
                    'attribute.userAccountAttribute.typeValue.' . UserAccountAttribute::TYPE_DATE     => UserAccountAttribute::TYPE_DATE,
                    'attribute.userAccountAttribute.typeValue.' . UserAccountAttribute::TYPE_DATETIME => UserAccountAttribute::TYPE_DATETIME,
                    'attribute.userAccountAttribute.typeValue.' . UserAccountAttribute::TYPE_NUMBER   => UserAccountAttribute::TYPE_NUMBER,
                    'attribute.userAccountAttribute.typeValue.' . UserAccountAttribute::TYPE_TEXT     => UserAccountAttribute::TYPE_TEXT,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CreateEditUserAccountAttribute::class);
        $resolver->setDefault('label_format', 'attribute.userAccountAttribute.%name%');
    }
}
