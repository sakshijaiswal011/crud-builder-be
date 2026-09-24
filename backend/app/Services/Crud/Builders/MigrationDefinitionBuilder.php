<?php

namespace App\Services\Crud\Builders;

use App\Models\CrudField;
use App\Models\CrudModule;

class MigrationDefinitionBuilder
{
    /**
     * @return array{table: string, columns: string, soft_deletes: string}
     */
    public function build(CrudModule $module): array
    {
        $module->loadMissing('fields');

        $columns = $module->fields
            ->map(fn (CrudField $field) => $this->buildColumn($field))
            ->implode("\n");

        if ($columns === '') {
            $columns = '            //';
        }

        return [
            'table' => $module->table_name,
            'columns' => $columns,
            'soft_deletes' => $module->soft_delete
                ? "\n            \$table->softDeletes();"
                : '',
        ];
    }

    protected function buildColumn(CrudField $field): string
    {
        $name = $field->field_name;
        $type = strtolower((string) $field->type);
        $line = '            $table';

        $line .= match ($type) {
            'string', 'varchar' => $field->length
                ? "->string('{$name}', {$field->length})"
                : "->string('{$name}')",
            'char' => $field->length
                ? "->char('{$name}', {$field->length})"
                : "->char('{$name}')",
            'text' => "->text('{$name}')",
            'longtext', 'long_text' => "->longText('{$name}')",
            'mediumtext', 'medium_text' => "->mediumText('{$name}')",
            'integer', 'int' => "->integer('{$name}')",
            'biginteger', 'big_integer', 'bigint' => "->bigInteger('{$name}')",
            'unsignedbiginteger', 'unsigned_big_integer' => "->unsignedBigInteger('{$name}')",
            'smallinteger', 'small_integer' => "->smallInteger('{$name}')",
            'tinyinteger', 'tiny_integer' => "->tinyInteger('{$name}')",
            'decimal' => "->decimal('{$name}', ".($field->length ?: 10).", 2)",
            'float' => "->float('{$name}')",
            'double' => "->double('{$name}')",
            'boolean', 'bool' => "->boolean('{$name}')",
            'date' => "->date('{$name}')",
            'datetime' => "->dateTime('{$name}')",
            'timestamp' => "->timestamp('{$name}')",
            'time' => "->time('{$name}')",
            'json' => "->json('{$name}')",
            'uuid' => "->uuid('{$name}')",
            'ulid' => "->ulid('{$name}')",
            'foreignid', 'foreign_id' => "->foreignId('{$name}')",
            default => "->string('{$name}')",
        };

        if ($field->nullable) {
            $line .= '->nullable()';
        }

        if ($field->default_value !== null && $field->default_value !== '') {
            $line .= '->default('.$this->formatDefault($field->default_value, $type).')';
        }

        if ($field->is_unique) {
            $line .= '->unique()';
        } elseif ($field->is_indexed) {
            $line .= '->index()';
        }

        if ($field->comment) {
            $line .= "->comment('".addslashes($field->comment)."')";
        }

        return $line.';';
    }

    protected function formatDefault(string $value, string $type): string
    {
        if (in_array($type, ['boolean', 'bool'], true)) {
            return in_array(strtolower($value), ['1', 'true', 'yes'], true) ? 'true' : 'false';
        }

        $numeric = [
            'integer', 'int', 'biginteger', 'big_integer', 'bigint',
            'unsignedbiginteger', 'unsigned_big_integer',
            'smallinteger', 'small_integer', 'tinyinteger', 'tiny_integer',
            'decimal', 'float', 'double',
        ];

        if (in_array($type, $numeric, true) && is_numeric($value)) {
            return $value;
        }

        return "'".addslashes($value)."'";
    }
}
