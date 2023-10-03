<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Lien logique entre les champs heureDebut et heureFin :
 * la date de fin doit être supérieure à la date de début.
 * @Annotation
 */
class DateGreaterThanOrEqual extends Constraint
{
    public $message = 'La date de fin devrait être supérieure ou égale à la date de début.';
    public $startDateField = 'heureDebut';
    public $endDateField = 'heureFin';
}
