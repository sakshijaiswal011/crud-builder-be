<?php

namespace App\Console\Commands;

use App\Models\CrudModule;
use App\Services\Crud\CrudGenerator;
use Illuminate\Console\Command;

class CrudRegenerateCommand extends Command
{
    protected $signature = 'crud:regenerate {module? : Module id or slug (omit to regenerate all)}';

    protected $description = 'Regenerate application files for one or all CRUD modules';

    public function handle(CrudGenerator $generator): int
    {
        $key = $this->argument('module');

        $modules = CrudModule::query()
            ->when($key, function ($q) use ($key) {
                $q->when(
                    is_numeric($key),
                    fn ($q) => $q->where('id', $key),
                    fn ($q) => $q->where('slug', $key)
                );
            })
            ->get();

        if ($modules->isEmpty()) {
            $this->error($key ? "Module [{$key}] not found." : 'No CRUD modules found.');

            return self::FAILURE;
        }

        foreach ($modules as $module) {
            $this->info("Regenerating [{$module->name}]...");
            $generator->generate($module);
            $this->line('  done.');
        }

        $this->info('Regeneration complete.');

        return self::SUCCESS;
    }
}
