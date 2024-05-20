<?php

use Illuminate\Support\Str;
use Laravel\Prompts\Prompt;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

use Laravel\Prompts\TextPrompt;
use Laravel\Prompts\ConfirmPrompt;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

require __DIR__.'/vendor/autoload.php';

Prompt::fallbackWhen(windows_os());

$input = new Symfony\Component\Console\Input\ArgvInput();
$output = new Symfony\Component\Console\Output\ConsoleOutput();

TextPrompt::fallbackUsing(function (TextPrompt $prompt) use ($input, $output) {
    $question = (new Question($prompt->label, $prompt->default ?: null))
        ->setValidator(function ($answer) use ($prompt) {
            if ($prompt->required && $answer === null) {
                throw new \RuntimeException(is_string($prompt->required) ? $prompt->required : 'Required.');
            }

            if ($prompt->validate) {
                $error = ($prompt->validate)($answer ?? '');

                if ($error) {
                    throw new \RuntimeException($error);
                }
            }

            return $answer;
        });

    return (new SymfonyStyle($input, $output))
        ->askQuestion($question);
});

ConfirmPrompt::fallbackUsing(function (ConfirmPrompt $prompt) use ($input, $output) {
    $question = (new \Symfony\Component\Console\Question\ConfirmationQuestion($prompt->label, $prompt->default))
        ->setValidator(function ($answer) use ($prompt) {
            if ($prompt->required && $answer === null) {
                throw new \RuntimeException(is_string($prompt->required) ? $prompt->required : 'Required.');
            }

            if ($prompt->validate) {
                $error = ($prompt->validate)($answer);

                if ($error) {
                    throw new \RuntimeException($error);
                }
            }

            return $answer;
        });

    return (new SymfonyStyle($input, $output))
        ->askQuestion($question);
});

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
        makeModel($package, $namespace);
    }
    if ($has_web_routes) {
        makeWebRoutes($package);
    }

    makeServiceProvider($package, $namespace, $has_config, $has_migration, $has_web_routes);
    setComposerName($company, $package, $namespace);
    initGit();

    removeStubDir();
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

function makeModel(string $package, string $namespace): void
{
    $model = file_get_contents('stubs/model.stub');
    $model = Str::replace('Danidoble\LaravelPackageSkeleton', $namespace, $model);
    $model = Str::replace('LaravelPackageSkeleton', Str::studly($package), $model);
    file_put_contents('src/Models/'.Str::studly($package).'.php', $model);
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

function removeStubDir(): void
{
    $files = glob('stubs/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    rmdir('stubs');
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
        label: 'Do you want a config file?',
        default: true,
        yes: 'Yes',
        no: 'No',
        hint: 'This make a file inside config dir.'
    );
}

function wantMigration(): bool
{
    return confirm(
        label: 'Do you want a migration file?',
        default: true,
        yes: 'Yes',
        no: 'No',
        hint: 'This make a file inside database/migrations dir.'
    );
}

function wantWebRoutes(): bool
{
    return confirm(
        label: 'Do you want web routes?',
        default: true,
        yes: 'Yes',
        no: 'No',
        hint: 'This make a file inside routes dir.'
    );
}

function initGit(): void
{
    $initialize = confirm(
        label: 'Do you want initialize a git repository?',
        default: true,
        yes: 'Yes',
        no: 'No',
        hint: 'This will run git init.',
    );

    if (! file_exists('.gitignore')) {
        copy('stubs/gitignore.stub', '.gitignore');
    }

    if ($initialize) {
        exec('git init');
    }
}

configure();
