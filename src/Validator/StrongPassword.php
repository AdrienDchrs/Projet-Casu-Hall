<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * C'est une contrainte de validation personnalisée
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class StrongPassword extends Constraint
{
    public $message = 'Le mot de passe doit comporter au moins 8 caractères, incluant au moins une majuscule, une minuscule, un chiffre et un caractère spécial.';

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
?>