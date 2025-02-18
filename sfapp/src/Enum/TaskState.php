<?php

namespace App\Enum;

/**
 * @brief All tasks states
 */
enum TaskState: string
{
    case NOT_ASSOCIATED = 'NON-ASSOCIÉ';
    case TO_TREAT = 'À TRAITER';
    case DOING = 'EN COURS';
    case COMPLETED = 'TERMINÉ';
}