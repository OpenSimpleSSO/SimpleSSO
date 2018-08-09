<?php

namespace App\Form\Admin\Client;

use App\Model\Data\Admin\Client\CreateEditClient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateEditClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('publicKey', TextareaType::class)
            ->add('url', UrlType::class)
            ->add('redirectPath', TextType::class)
            ->add('logoutPath', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CreateEditClient::class);
        $resolver->setDefault('label_format', 'attribute.client.%name%');
    }
}
