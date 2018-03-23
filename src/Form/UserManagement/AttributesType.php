<?php

namespace App\Form\UserManagement;

use App\Entity\UserAccountAttribute;
use App\Model\UserAccountAttributeModel;
use LogicException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributesType extends AbstractType
{
    /**
     * @var UserAccountAttributeModel
     */
    private $model;

    /**
     * AttributesType constructor.
     *
     * @param UserAccountAttributeModel $model
     */
    public function __construct(UserAccountAttributeModel $model)
    {
        $this->model = $model;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->model->get() as $attribute) {
            $options = [
                'label'    => $attribute->title,
                'required' => false,
            ];
            if ($attribute->type === UserAccountAttribute::TYPE_DATE || $attribute->type === UserAccountAttribute::TYPE_DATETIME) {
                $options['widget'] = 'single_text';
            }
            $builder->add($attribute->key, $this->resolveFormType($attribute->type), $options);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('label', false);
        $resolver->setDefault('translation_domain', false);
    }

    private function resolveFormType(string $type): string
    {
        switch ($type) {
            case UserAccountAttribute::TYPE_BOOL:
                return CheckboxType::class;

            case UserAccountAttribute::TYPE_DATE:
                return DateType::class;

            case UserAccountAttribute::TYPE_DATETIME:
                return DateTimeType::class;

            case UserAccountAttribute::TYPE_NUMBER:
                return NumberType::class;

            case UserAccountAttribute::TYPE_TEXT:
                return TextareaType::class;

            default:
                throw new LogicException('Unknown attribute type.');
        }
    }
}
