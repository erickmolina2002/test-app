<?php

namespace App\DTOs\Attributes;

use App\Domain\ValueObjects\Cpf;
use Attribute;
use Spatie\LaravelData\Attributes\Validation\CustomValidationAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ValidCpf extends CustomValidationAttribute
{
    public function getRules(mixed $path): array
    {
        return [
            function (string $attribute, mixed $value, \Closure $fail) {
                if (! Cpf::isValid($value)) {
                    $fail('O CPF informado é inválido.');
                }
            },
        ];
    }
}
