<?php

namespace App\Services\Crud\Builders;

use App\Models\CrudFormList;
use App\Models\CrudModule;

class ValidationDefinitionBuilder
{
    /**
     * @return array{store_rules: string, update_rules: string}
     */
    public function build(CrudModule $module): array
    {
        $module->loadMissing(['formLists.field', 'fields']);

        $storeLines = [];
        $updateLines = [];

        foreach ($module->formLists as $form) {
            $fieldName = $form->field?->field_name;
            if (! $fieldName) {
                continue;
            }

            $storeRules = $this->normalizeRules($form, forUpdate: false);
            $updateRules = $this->normalizeRules($form, forUpdate: true);

            $storeLines[] = "            '{$fieldName}' => [".$this->ruleArray($storeRules).'],';
            $updateLines[] = "            '{$fieldName}' => [".$this->ruleArray($updateRules).'],';
        }

        if (empty($storeLines)) {
            $storeLines[] = '            //';
            $updateLines[] = '            //';
        }

        return [
            'store_rules' => implode("\n", $storeLines),
            'update_rules' => implode("\n", $updateLines),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeRules(CrudFormList $form, bool $forUpdate): array
    {
        $rules = $form->validation_rules ?? [];

        if (is_string($rules)) {
            $rules = array_values(array_filter(array_map('trim', explode('|', $rules))));
        }

        if (! is_array($rules)) {
            $rules = [];
        }

        $rules = array_values(array_unique(array_map('strval', $rules)));

        if ($form->is_required && ! in_array('required', $rules, true) && ! in_array('nullable', $rules, true)) {
            array_unshift($rules, 'required');
        }

        if ($forUpdate) {
            $rules = array_map(function ($rule) {
                return $rule === 'required' ? 'sometimes' : $rule;
            }, $rules);

            if (! in_array('sometimes', $rules, true)) {
                array_unshift($rules, 'sometimes');
            }
        }

        return empty($rules) ? ['nullable'] : $rules;
    }

    /**
     * @param  array<int, string>  $rules
     */
    protected function ruleArray(array $rules): string
    {
        return collect($rules)
            ->map(fn ($rule) => "'{$rule}'")
            ->implode(', ');
    }
}
