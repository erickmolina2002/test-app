<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Cpf
{
    public readonly string $value;

    public function __construct(string $cpf)
    {
        $cleaned = self::sanitize($cpf);

        if (! self::isValid($cleaned)) {
            throw new InvalidArgumentException('O CPF informado é inválido.');
        }

        $this->value = $cleaned;
    }

    public static function sanitize(string $cpf): string
    {
        return preg_replace('/\D/', '', $cpf);
    }

    public static function isValid(string $cpf): bool
    {
        $cpf = self::sanitize($cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += $cpf[$i] * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;

            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
