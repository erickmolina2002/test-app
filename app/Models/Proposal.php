<?php

namespace App\Models;

use App\Casts\CpfCast;
use App\Enums\ProposalStatus;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $fillable = [
        'cpf',
        'nome',
        'data_nascimento',
        'valor_emprestimo',
        'chave_pix',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'cpf' => CpfCast::class,
            'data_nascimento' => 'date',
            'valor_emprestimo' => 'decimal:2',
            'status' => ProposalStatus::class,
        ];
    }
}
