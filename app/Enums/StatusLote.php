<?php

namespace App\Enums;

enum StatusLote: string
{
    case ABERTO = 'aberto';
    case ENCERRADO = 'encerrado';
    case CANCELADO = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::ABERTO => 'Aberto',
            self::ENCERRADO => 'Encerrado',
            self::CANCELADO => 'Cancelado',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::ABERTO => 'success',
            self::ENCERRADO => 'secondary',
            self::CANCELADO => 'danger',
        };
    }
}
