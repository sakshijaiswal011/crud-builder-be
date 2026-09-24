<?php

namespace App\Console\Commands;

use App\Models\CrudModule;
use App\Services\Crud\CrudGenerator;
use Illuminate\Console\Command;

class CrudGenerateCommand extends Command
{
    protected $signature = 'crud:generate {module : Module id or slug}';

    protected $description = 'Generate application files for an existing CRUD module';

    public function handle(CrudGenerator $generator): int
    {
        $key = $this->argument('module');

        $module = CrudModule::query()
            ->when(
                is_numeric($key),
                fn ($q) => $q->where('id', $key),
                fn ($q) => $q->where('slug', $key)
            )
            ->first();

        if (! $module) {
            $this->error("Module [{$key}] not found.");

            return self::FAILURE;
        }

        $generated = $generator->generate($module);

        $this->info("Generated files for [{$module->name}]:");
        foreach ($generated as $type => $path) {
            if (is_array($path)) {
                foreach ($path as $label => $file) {
                    $this->line("  - {$type}.{$label}: {$file}");
                }
            } else {
                $this->line("  - {$type}: {$path}");
            }
        }

        return self::SUCCESS;
    }
}
