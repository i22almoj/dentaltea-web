<?php
 
namespace App\Security\Hasher;
 
use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\PasswordHasher\Hasher\CheckPasswordLengthTrait;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
 
class MD5PasswordHasher implements PasswordHasherInterface
{
    use CheckPasswordLengthTrait;
 
    public function hash(string $plainPassword): string
    {
        if ($this->isPasswordTooLong($plainPassword)) {
            throw new InvalidPasswordException();
        }
 
        return md5($plainPassword);
    }
 
    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        if ('' === $plainPassword || $this->isPasswordTooLong($plainPassword)) {
            return false;
        }
 
        return md5($plainPassword) === $hashedPassword;
    }
 
    public function needsRehash(string $hashedPassword): bool
    {
        return false;
    }
}