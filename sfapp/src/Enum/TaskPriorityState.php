<?php

namespace App\Enum;

/**
* @brief All tasks states
*/
enum TaskPriorityState: string
{
case HIGH = 'HAUTE';
case MEDIUM = 'MOYENNE';
case LOW = 'BASSE';
}