<?php 
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class UniqueEmail extends Constraint
{
    public $message = 'El email ya ha sido utilizado por otro usuario.';
    public $mode = 'strict'; 

    public function getRequiredOptions(): array
    {
        return ['mode'];
    }


    public function validatedBy(): ?string
    {
        return static::class.'Validator';
    }
}