<?php

namespace App\Services\Crud\Support;

class StubRenderer
{
    public function render(string $stub, array $replacements): string
    {
        $search = [];
        $replace = [];

        foreach ($replacements as $key => $value) {
            $search[] = '{{ '.$key.' }}';
            $replace[] = (string) $value;
        }

        return str_replace($search, $replace, $stub);
    }

    public function renderFile(string $stubPath, string $destination, array $replacements): string
    {
        if (! is_file($stubPath)) {
            throw new \RuntimeException("Stub not found: {$stubPath}");
        }

        $content = $this->render(
            (string) file_get_contents($stubPath),
            $replacements
        );

        $directory = dirname($destination);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($destination, $content);

        return $destination;
    }

    public function stubPath(string $name): string
    {
        return resource_path('crud-stubs/'.$name);
    }
}
