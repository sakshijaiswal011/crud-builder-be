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
        $module->loadMissing(['fields', 'relationships.relatedModule']);

        $columns = $module->fields
            ->map(fn (CrudField $field) => $this->buildColumn($field, $module))
            ->implode("\n");

        if ($columns === '') {
            $columns = '            //';
        }

        $foreignKeys = $this->buildForeignKeys($module);
        if ($foreignKeys !== '') {
            $columns .= "\n\n" . $foreignKeys;
        }

        return [
            'table' => $module->table_name,
            'columns' => $columns,
            'soft_deletes' => $module->soft_delete
                ? "\n            \$table->softDeletes();"
                : '',
        ];
    }

    protected function buildForeignKeys(CrudModule $module): string
    {
        $foreignKeys = [];

        foreach ($module->relationships as $relationship) {
            if ($relationship->relation_type === 'belongsTo' && $relationship->foreign_key && $relationship->relatedModule) {
                $foreign = $relationship->foreign_key;
                $onTable = $relationship->relatedModule->table_name;
                $references = $relationship->local_key ?: 'id';

                $foreignKeys[] = "            \$table->foreign('{$foreign}')->references('{$references}')->on('{$onTable}')->restrictOnDelete();";
            }
        }

        return implode("\n", $foreignKeys);
    }

    public function buildColumn(CrudField $field, ?CrudModule $module = null): string
    {
        $name = $field->field_name;
        $type = strtolower((string) $field->type);
        $line = '            $table';

        // Check if this field is used as a foreign key in any belongsTo relationship
        $isForeignKey = false;
        if ($module) {
            foreach ($module->relationships as $relationship) {
                if ($relationship->relation_type === 'belongsTo' && $relationship->foreign_key === $name) {
                    $isForeignKey = true;
                    break;
                }
            }
        }

        // Force foreign keys to unsignedBigInteger to match Laravel's default bigIncrements('id')
        if ($isForeignKey) {
            $type = 'unsignedbiginteger';
        }

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
