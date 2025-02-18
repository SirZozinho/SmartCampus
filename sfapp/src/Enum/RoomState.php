<?php

namespace App\Enum;

/**
 * @brief All room states
 */
enum RoomState: string
{
    case EQUIPED = 'ÉQUIPÉ';
    case AVAILABLE = 'DISPONIBLE';
    case UNAVAILABLE = 'INDISPONIBLE';
}