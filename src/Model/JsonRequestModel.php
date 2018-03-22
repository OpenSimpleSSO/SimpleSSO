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
     * JsonRequestModel constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Request $request
     * @param string  $dataClass
     * @return mixed
     */
    public function handleRequest(Request $request, string $dataClass)
    {
        $content = json_decode($request->getContent(), true);
        if ($content === false) {
            throw new BadRequestHttpException('Invalid or missing JSON content.');
        }

        $data = new $dataClass;
        foreach ($content as $key => $value) {
            $data->$key = $value;
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
