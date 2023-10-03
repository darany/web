<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DateGreaterThanOrEqualValidator extends ConstraintValidator
{
    /**
     * Vérifier que la date de fin est supérieure à la date de début.
     *
     * @param [type] $value
     * @param Constraint $constraint
     * @return void
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof DateGreaterThanOrEqual) {
            throw new UnexpectedTypeException($constraint, DateGreaterThanOrEqual::class);
        }

        $startDateValue = $this->context->getRoot()[$constraint->startDateField]->getData();
        $endDateValue = $this->context->getRoot()[$constraint->endDateField]->getData();

        if ($startDateValue && $endDateValue && $startDateValue < $endDateValue) {
            $this->context->buildViolation($constraint->message)
                ->atPath($constraint->endDateField)
                ->addViolation();
        }
    }
}
