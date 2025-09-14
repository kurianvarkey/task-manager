<?php

/**
 * Database logger
 * Code credits from Net4Ideas (http://www.net4ideas.com)
 *
 * @version  1.0.0
 *
 * @author   K V P <kurianvarkey@yahoo.com>
 *
 * @link     http://www.net4ideas.com
 */

declare(strict_types=1);

namespace App\Helpers\Profiler;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DbLogger
{
    private array $arrSql = [];

    /**
     * Records all database queries and logs them to the default log channel.
     * The log format is: "{sql} ({time} ms)"
     */
    public function record(string ...$skipPatterns): void
    {
        DB::listen(function (QueryExecuted $query) use ($skipPatterns) {
            foreach ($skipPatterns as $pattern) {
                if (stripos($query->sql, $pattern) !== false) {
                    return;
                }
            }

            $sqlData = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ];

            $this->arrSql[] = $sqlData;

            Log::info($this->format($sqlData));
        });
    }

    /**
     * Clears the list of executed queries.
     */
    public function clear(): void
    {
        $this->arrSql = [];
    }

    /**
     * Returns an array of all executed queries, each containing the execution time and the
     * formatted SQL query (with bindings).
     */
    public function list(): array
    {
        return array_map(function ($data) {
            return [
                'time' => $data['time'],
                'sql' => $this->format($data),
            ];
        }, $this->arrSql);
    }

    /**
     * Get the last executed SQL query.
     */
    public function getLastSql(): string
    {
        return $this->format(end($this->arrSql) ?: []);
    }

    /**
     * Format SQL query with bindings and execution time.
     */
    private function format(array $data): string
    {
        $sql = $data['sql'] ?? '';
        foreach ($data['bindings'] ?? [] as $binding) {
            $binding = is_numeric($binding) ? $binding : "'" . addslashes((string) $binding) . "'";
            $sql = preg_replace('/\?/', (string) $binding, $sql, 1);
            $sql = str_replace('"', '', $sql);
        }

        return $sql . ' (' . number_format($data['time'] ?? '', 2) . ' ms)';
    }
}
