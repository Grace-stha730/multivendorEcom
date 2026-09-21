<?php

namespace App\Livewire\Concerns;

use Livewire\WithPagination;

/**
 * Page-number bar for list pages. Admin/shop panels get the emerald bar,
 * the customer site gets the indigo one. Render it with {{ $rows->links() }}.
 */
trait PaginatesList
{
    use WithPagination;

    public function paginationView()
    {
        return str_contains(static::class, '\\User\\')
            ? 'components.pagination.site'
            : 'components.pagination.panel';
    }
}
