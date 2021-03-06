<?php

namespace JeroenNoten\LaravelMenu;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use JeroenNoten\LaravelAdminLte\ServiceProvider as AdminLteServiceProvider;
use JeroenNoten\LaravelMenu\Models\MenuItem;
use JeroenNoten\LaravelPackageHelper\ServiceProviderTraits;

class ServiceProvider extends BaseServiceProvider
{
    use ServiceProviderTraits;

    public function boot(Repository $config, Dispatcher $events, Routing $routing)
    {
        $this->loadViews();
        $this->publishConfig();
        $this->publishMigrations();
        $this->registerMenuFromConfig($config);
        $this->registerMenuFromDatabase();
        $routing->registerRoutes();

        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {
            $event->menu->add([
                'text' => 'Menu',
                'url' => 'admin/menu'
            ]);
        });
    }

    public function register()
    {
        $this->app->singleton(MenuBuilder::class);
        $this->app->register(AdminLteServiceProvider::class);
    }

    private function registerMenuFromConfig(Repository $config)
    {
        Menu::register(function () use ($config) {
            return $config->get('menu.menus.main');
        });
    }

    protected function path()
    {
        return __DIR__ . '/..';
    }

    protected function name()
    {
        return 'menu';
    }

    private function registerMenuFromDatabase()
    {
        Menu::register(function () {
            return MenuItem::all()->all();
        });
    }

    /**
     * Return the container instance
     *
     * @return Container
     */
    protected function getContainer()
    {
        return $this->app;
    }
}