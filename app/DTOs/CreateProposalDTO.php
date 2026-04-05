<?php

namespace App\DTOs;

use App\DTOs\Attributes\ValidCpf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class CreateProposalDTO extends Data
{
    public function __construct(
        // #[StringType, ValidCpf]
        #[StringType]
        public string $cpf,

        #[StringType, Max(255)]
        public string $nome,

        public string $dataNascimento,

        public float $valorEmprestimo,

        #[StringType, Max(255)]
        public string $chavePix,
    ) {}

    public static function rules(): array
    {
        return [
            'cpf' => ['required', 'string', 'regex:/^\d+$/'],
            'nome' => ['required', 'string', 'max:255'],
            'data_nascimento' => ['required', 'date', 'before:today'],
            'valor_emprestimo' => ['required', 'numeric', 'min:0.01'],
            'chave_pix' => ['required', 'string', 'max:255'],
        ];
    }

    public static function messages(): array
    {
        return [
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.regex' => 'O CPF deve conter apenas números.',
            'nome.required' => 'O nome é obrigatório.',
            'data_nascimento.required' => 'A data de nascimento é obrigatória.',
            'data_nascimento.date' => 'A data de nascimento deve ser uma data válida.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'valor_emprestimo.required' => 'O valor do empréstimo é obrigatório.',
            'valor_emprestimo.numeric' => 'O valor do empréstimo deve ser numérico.',
            'valor_emprestimo.min' => 'O valor do empréstimo deve ser maior que zero.',
            'chave_pix.required' => 'A chave PIX é obrigatória.',
        ];
    }
}
