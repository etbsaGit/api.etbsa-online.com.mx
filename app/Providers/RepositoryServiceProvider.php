<?php

namespace App\Providers;

use App\Contracts\BrandContract;
use App\Contracts\ProductContract;
use App\Contracts\VendorContract;
use App\Repositories\BrandRepository;
use App\Repositories\ProductRepository;
use App\Repositories\VendorRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    protected array $repositories = [
        BrandContract::class => BrandRepository::class,
        VendorContract::class => VendorRepository::class,
        ProductContract::class => ProductRepository::class,
    ];

	public function register()
	{
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
	}
}
