<?php

namespace App\Providers;

use App\Contracts\BrandContract;
use App\Contracts\CategoryContract;
use App\Contracts\FeatureContract;
use App\Contracts\ProductContract;
use App\Contracts\VendorContract;
use App\Repositories\BrandRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\FeatureRepository;
use App\Repositories\ProductRepository;
use App\Repositories\VendorRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    protected array $repositories = [
        BrandContract::class => BrandRepository::class,
        VendorContract::class => VendorRepository::class,
        CategoryContract::class => CategoryRepository::class,
        FeatureContract::class => FeatureRepository::class,
        ProductContract::class => ProductRepository::class,
    ];

	public function register()
	{
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
	}
}
