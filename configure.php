<?php

use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

require __DIR__.'/vendor/autoload.php';

function configure(): void
{
    $company = getcompany();
    $package = getPackageName();
    $namespace = getNamespace($company, $package);
    $has_config = wantConfig();
    $has_migration = wantMigration();
    $has_web_routes = wantWebRoutes();
    if ($has_config) {
        makeConfigFile($package);
    }
    if ($has_migration) {
        makeTableMigration($package);
    }
    if ($has_web_routes) {
        makeWebRoutes($package);
    }

    makeServiceProvider($package, $namespace, $has_config, $has_migration, $has_web_routes);
    setComposerName($company, $package, $namespace);
}

function setComposerName(string $company, string $package, string $namespace): void
{
    $composer = json_decode(file_get_contents('composer.json'), true);

    $company = Str::lower($company);
    $package = Str::lower($package);

    $packageProvider = Str::studly($package).'ServiceProvider';

    $composer['name'] = $company.'/'.Str::snake($package);
    $composer['authors'] = [];
    $composer['autoload']['psr-4'] = [
        $namespace.'\\' => 'src/',
    ];
    unset($composer['autoload-dev']['psr-4']['Danidoble\\LaravelPackageSkeleton\\Tests\\']);
    $composer['autoload-dev']['psr-4'][$namespace.'\\Tests\\'] = 'tests/';
    $composer['extra']['laravel']['providers'] = [
        $namespace.'\\Providers\\'.$packageProvider,
    ];

    // when install the package, execute this file to configure the package
    $composer['scripts']['post-install-cmd'][] = 'php configure.php';

    file_put_contents('composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function getNamespace(string $company, string $package): string
{
    $company = Str::studly($company);
    $package = Str::studly($package);

    return $company.'\\'.$package;
}

function getConfigName(string $package): string
{
    return Str::kebab($package);
}

function getTableName(string $package): string
{
    return Str::snake($package);
}
function getTableMigrationName(string $package): string
{
    return now()->format('Y_m_d_His').'_create_'.Str::snake($package).'_table';
}

function makeConfigFile(string $package): void
{
    $config = file_get_contents('stubs/config.stub');
    file_put_contents('config/'.getConfigName($package).'.php', $config);
}

function makeTableMigration(string $package): void
{
    $table = file_get_contents('stubs/migration.stub');
    $table = str_replace('[package]', getTableName($package), $table);
    file_put_contents('database/migrations/'.getTableMigrationName($package).'.php', $table);
}

function makeWebRoutes(string $package): void
{
    makeView($package);
    makeWebRoutesFile($package);
}

function makeWebRoutesFile(string $package): void
{
    $routes = file_get_contents('stubs/web_routes.stub');
    $routes = Str::replace('package', Str::snake($package), $routes);
    file_put_contents('routes/web.php', $routes);
}

function makeView(string $package): void
{
    $view = file_get_contents('stubs/view.stub');

    $view = Str::replace('Package', $package, $view);
    $view = Str::replace('[package]', Str::snake($package), $view);
    file_put_contents('resources/views/'.Str::snake($package).'.blade.php', $view);
}

function makeServiceProvider(
    string $package,
    string $namespace,
    bool $has_config,
    bool $has_migration,
    bool $has_web_routes
): void {
    $provider = file_get_contents('stubs/provider.stub');
    $provider = Str::replace('Danidoble\LaravelPackageSkeleton', $namespace, $provider);
    $provider = Str::replace('LaravelPackageSkeletonServiceProvider', Str::studly($package).'ServiceProvider', $provider);

    $provider = Str::replace(
        '$this->mergeConfigFrom(__DIR__.\'/../../config/package.php\', \'package\');',
        $has_config ? '$this->mergeConfigFrom(__DIR__.\'/../../config/'.getConfigName($package).'.php\', \''.Str::snake($package).'\');' : '',
        $provider
    );

    $provider = Str::replace(
        '$this->loadMigrationsFrom(__DIR__.\'/../../database/migrations\');',
        $has_migration ? '$this->loadMigrationsFrom(__DIR__.\'/../../database/migrations\');' : '',
        $provider
    );

    $provider = Str::replace(
        '$this->loadViewsFrom(__DIR__.\'/../../resources/views\', \'package\');',
        $has_web_routes ? '$this->loadViewsFrom(__DIR__.\'/../../resources/views\', \''.Str::snake($package).'\');' : '',
        $provider
    );

    $provider = Str::replace(
        '$this->loadRoutesFrom(__DIR__.\'/../../routes/web.php\');',
        $has_web_routes ? '$this->loadRoutesFrom(__DIR__.\'/../../routes/web.php\');' : '',
        $provider
    );

    file_put_contents('src/Providers/'.Str::studly($package).'ServiceProvider.php', $provider);
}

function getCompany(): string
{
    return text(
        label: 'What is your company?',
        placeholder: 'E.g. Acme',
        required: 'Company is required.',
        hint: 'This will be used to namespace the package.',
    );
}

function getPackageName(): string
{
    return text(
        label: 'What is your package name?',
        placeholder: 'E.g. Package',
        required: 'Name of package is required.',
        hint: 'This will be used to name the package.',
    );
}

function wantConfig(): bool
{
    return confirm(
        label: 'Do you want have a config file?',
        default: false,
        yes: 'Yes',
        no: 'No',
        hint: 'This make a file inside config dir.'
    );
}

function wantMigration(): bool
{
    return confirm(
        label: 'Do you want have a migration file?',
        default: false,
        yes: 'Yes',
        no: 'No',
        hint: 'This make a file inside database/migrations dir.'
    );
}

function wantWebRoutes(): bool
{
    return confirm(
        label: 'Do you want web routes?',
        default: false,
        yes: 'Yes',
        no: 'No',
        hint: 'This make a file inside routes dir.'
    );
}

configure();
