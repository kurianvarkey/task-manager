<?php

/**
 * General exception class
 * Code credits from Net4Ideas (http://www.net4ideas.com)
 *
 * @version  1.0.0
 *
 * @author   K V P <kurianvarkey@yahoo.com>
 *
 * @link     http://www.net4ideas.com
 */

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class GeneralException extends Exception
{
    /**
     * Report the exception.
     * return true if custom reporting is needed
     *
     * @codeCoverageIgnore
     */
    public function report(): bool
    {
        return true;
    }
}
