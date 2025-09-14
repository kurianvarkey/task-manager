<?php

/**
 * ServiceBase - base class for all services
 * Code credits from Net4Ideas (http://www.net4ideas.com)
 *
 * @version  1.0.0
 *
 * @author   K V P <kurianvarkey@yahoo.com>
 *
 * @link     http://www.net4ideas.com
 */

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * ServiceBase class
 */
abstract class ServiceBase implements IServiceRepository
{
    /**
     * The default pagination limit for records.
     */
    public const DEFAULT_PAGINATION_LIMIT = 25;

    /**
     * The default pagination limit for records.
     */
    public const MAX_PAGINATION_LIMIT = 50;

    /**
     * The default number of times to retry a database operation.
     */
    public const NUM_DB_TRIES = 3;

    /**
     * The retry after interval
     */
    public const RETRY_AFTER = 100000; // 100ms

    /**
     * Construct the service
     */
    public function __construct() {}

    /**
     * Get the sortable fields
     */
    abstract public function sortableFields(): array;

    /**
     * Get the default pagination limit
     */
    public function getDefaultLimit(): int
    {
        return self::DEFAULT_PAGINATION_LIMIT;
    }

    /**
     * Get the sortable fields
     */
    protected function getSortableField(?string $field): ?string
    {
        if (is_null($field)) {
            return null;
        }

        return in_array($field, $this->sortableFields()) ? $field : null;
    }

    /**
     * Calling db transactions with the number of tries
     *
     * @param  mixed  $callback
     * @return mixed|exception
     */
    protected function doDbTransaction($callback, bool $retryOnUniqueError = false)
    {
        $maxAttempts = self::NUM_DB_TRIES;
        $attempts = 0;

        do {
            try {
                return DB::transaction($callback, $maxAttempts);
            } catch (QueryException $e) {
                $attempts++;

                // Check if it's a unique constraint violation
                if ($attempts >= $maxAttempts || ! $retryOnUniqueError || ! str_contains($e->getMessage(), 'UNIQUE')) {
                    throw $e;
                }

                // sleep for a bit to reduce contention
                usleep(self::RETRY_AFTER);
            }
        } while ($attempts < $maxAttempts);
    }
}
