<?php

namespace App\Providers;

use App\Contracts\Data\EventRepositoryInterface;
use App\Repository\EventRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        EventRepositoryInterface::class => EventRepository::class,
    ];
}
