<?php

namespace App\Validation;

use Doctrine\Common\Annotations\Annotation;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation()
 */
class JsonConstraint extends Constraint
{
    public $message = 'Invalid JSON.';

    public function validatedBy()
    {
        return JsonValidator::class;
    }
}
