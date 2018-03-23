<?php

namespace App\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class JsonRequestModel
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var UserAccountAttributeModel
     */
    private $attributeModel;

    /**
     * JsonRequestModel constructor.
     *
     * @param ValidatorInterface        $validator
     * @param UserAccountAttributeModel $attributeModel
     */
    public function __construct(ValidatorInterface $validator, UserAccountAttributeModel $attributeModel)
    {
        $this->validator = $validator;
        $this->attributeModel = $attributeModel;
    }

    /**
     * @param Request $request
     * @param string  $dataClass
     * @return mixed
     */
    public function handleRequest(Request $request, string $dataClass)
    {
        $content = json_decode($request->getContent(), true);
        if (!$content) {
            throw new BadRequestHttpException('Invalid or missing JSON content.');
        }

        $data = new $dataClass;
        $hasExtraData = property_exists($dataClass, 'extraData');
        foreach ($content as $key => $value) {
            if (property_exists($dataClass, $key)) {
                $data->$key = $value;
            } elseif ($hasExtraData) {
                $data->extraData[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * @param mixed                            $data
     * @param ConstraintViolationListInterface $errors
     * @return bool
     */
    public function isValid($data, &$errors): bool
    {
        $errors = $this->validator->validate($data);

        return count($errors) === 0;
    }
}
