<?php

namespace App\Enum;

/**
 * @brief All acquiisition system states
 */
enum AcquisitionSystemState: string
{
    case FUNCTIONAL = 'FONCTIONNEL';

    case MAINTENANCE = 'MAINTENANCE';

    case NOT_ASSOCIATED = 'NON-ASSOCIÉ';

    case DEFAULTER = 'DÉFAILLANT';

    case OUT_OF_ORDER = 'HORS-SERVICE';
}