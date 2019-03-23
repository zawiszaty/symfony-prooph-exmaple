<?php

declare(strict_types=1);

namespace App;

use App\Infrastructure\Common\BusPass;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Prooph\Bundle\EventStore\ProophEventStoreBundle;
use Prooph\Bundle\ServiceBus\ProophServiceBusBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function registerBundles(): array
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new MonologBundle(),
            new DoctrineBundle(),
            new ProophEventStoreBundle(),
            new ProophServiceBusBundle(),
        ];

//        if ($this->getEnvironment() == 'dev') {
//
//        }

        return $bundles;
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/../config/package/framework.yaml');
        $loader->load(__DIR__.'/../config/package/monolog.yaml');
        $loader->load(__DIR__.'/../config/package/doctrine.yaml');
        $loader->load(__DIR__.'/../config/package/prooph_event_store.yaml');
        $loader->load(__DIR__.'/../config/package/prooph_service_bus.yaml');
        $loader->load(__DIR__.'/../config/package/routing.yaml');
        $loader->load(__DIR__.'/../config/services.yml');

        // configure WebProfilerBundle only if the bundle is enabled
        if (isset($this->bundles['WebProfilerBundle'])) {
            $c->loadFromExtension('web_profiler', [
                'toolbar' => true,
                'intercept_redirects' => false,
            ]);
        }
        $c->addCompilerPass(new BusPass());
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $confDir = $this->getProjectDir().'/config';
        $routes->import($confDir.'/{routes}/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}'.self::CONFIG_EXTS, '/', 'glob');
    }

    // optional, to use the standard Symfony cache directory
    public function getCacheDir()
    {
        return __DIR__.'/../var/cache/'.$this->getEnvironment();
    }

    // optional, to use the standard Symfony logs directory
    public function getLogDir()
    {
        return __DIR__.'/../var/log';
    }

    public function boot(): ?Kernel
    {
        $kernel = parent::boot(); // TODO: Change the autogenerated stub

        return $kernel;
    }
}
