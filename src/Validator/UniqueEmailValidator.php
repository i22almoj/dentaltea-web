<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use App\Repository\UserRepository;

/**
 * Class UserEmailValidator.
 */
class UniqueEmailValidator extends ConstraintValidator
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserEmailValidator constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Check if email is valid, then check if email already exists.
     *
     * @param mixed      $email
     * @param Constraint $constraint
     */
    public function validate($email, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueEmail) {
            throw new UnexpectedTypeException($constraint, UniqueEmail::class);
        }
        
            /** @var User $object */
            $object = $this->context->getObject();

            /** @var User $user */
            $user = $this->userRepository->findOneBy([
                'email' => $email,
            ]);

            if (!$user || ($user->getId() === $object->getId())) {
                return;
            }

            /* @var $constraint UserEmailConstraint */
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $email)
                ->addViolation();
      //  }
    }
}