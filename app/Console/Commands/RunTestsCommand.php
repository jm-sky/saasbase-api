<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

class RunTestsCommand extends Command
{
    protected $signature = 'tests:run {pattern? : The path pattern to run tests}';

    protected $description = 'Run tests with an optional path pattern.';

    public function handle()
    {
        $pattern = $this->argument('pattern');

        if (!$pattern) {
            $options = $this->getTestPaths();

            if (empty($options)) {
                $this->warn('No test files found.');

                return Command::FAILURE;
            }

            $pattern = $this->choice('Choose test path to run', $options);
        }

        $this->info("Running tests for pattern: {$pattern}");

        $command = "php artisan test {$pattern}";
        // This allows to see colors in the terminal, but works only on Linux
        $command = "script -q -c '{$command}' /dev/null";
        passthru($command, $exitCode);

        return $exitCode;
    }

    protected function getTestPaths(): array
    {
        $finder = new Finder();
        $finder->files()->in(base_path('tests'))->name('*.php');

        $paths = [];

        foreach ($finder as $file) {
            $relativePath = str_replace(base_path() . '/', '', $file->getRealPath());
            $paths[]      = $relativePath;
        }

        // Get all paths and first-level folders
        $allPaths = collect($paths)
            ->filter(fn ($p) => !str_contains($p, 'TestCase.php'))
            ->filter(fn ($p) => !str_contains($p, 'Traits'))
            ->map(fn ($p) => explode('/', $p))
            ->map(fn ($parts) => implode('/', array_slice($parts, 0, 3)))
            ->unique()
            ->sort()
            ->values()
        ;

        // Get first-level folders
        $firstLevelFolders = $allPaths
            ->map(fn ($p) => explode('/', $p)[0] . '/' . explode('/', $p)[1])
            ->unique()
            ->sort()
            ->values()
        ;

        // Combine first-level folders with other paths
        return $firstLevelFolders
            ->concat($allPaths)
            ->unique()
            ->values()
            ->toArray()
        ;
    }
}
