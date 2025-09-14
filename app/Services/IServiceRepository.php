<?php

/**
 * IServiceRepository - interface
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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * IServiceRepository
 */
interface IServiceRepository
{
    /**
     * Set sortable fields
     */
    public function sortableFields(): array;

    /**
     * store
     */
    public function store(array $data): ?Model;

    /**
     * update
     */
    public function update(int $id, array $data): ?Model;

    /**
     * find by id
     *
     * @return void
     */
    public function find(int $id): ?Model;

    /**
     * list
     */
    public function list(?array $filters = [], ?int $limit = null): LengthAwarePaginator;

    /**
     * Destroy by id
     *
     * @return void
     */
    public function delete(int $id): bool;
}
