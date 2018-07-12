<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class JsonValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     * @param JsonConstraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $decoded = json_decode($value);
        if ($decoded === null && $value !== 'null') {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
