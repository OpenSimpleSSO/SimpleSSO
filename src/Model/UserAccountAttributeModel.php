<?php

namespace App\Model;

use App\Entity\UserAccountAttribute;
use App\Model\Data\Admin\UserAccountAttribute\CreateEditUserAccountAttribute;
use App\Repository\UserAccountAttributeRepository;

class UserAccountAttributeModel
{
    /**
     * @var UserAccountAttributeRepository
     */
    private $repository;

    /**
     * UserAccountAttributeModel constructor.
     *
     * @param UserAccountAttributeRepository $repository
     */
    public function __construct(UserAccountAttributeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param CreateEditUserAccountAttribute $data
     * @return UserAccountAttribute
     */
    public function create(CreateEditUserAccountAttribute $data): UserAccountAttribute
    {
        $attribute = new UserAccountAttribute();
        $attribute->title = $data->title;
        $attribute->key = $data->key;
        $attribute->type = $data->type;
        $this->repository->save($attribute);

        return $attribute;
    }

    /**
     * @param UserAccountAttribute $attribute
     * @return CreateEditUserAccountAttribute
     */
    public function generateEditionData(UserAccountAttribute $attribute): CreateEditUserAccountAttribute
    {
        $data = new CreateEditUserAccountAttribute();
        $data->title = $attribute->title;
        $data->key = $attribute->key;
        $data->type = $attribute->type;

        return $data;
    }

    /**
     * @param UserAccountAttribute           $attribute
     * @param CreateEditUserAccountAttribute $data
     */
    public function edit(UserAccountAttribute $attribute, CreateEditUserAccountAttribute $data): void
    {
        $attribute->title = $data->title;
        $attribute->key = $data->key;
        $attribute->type = $data->type;
    }
}
