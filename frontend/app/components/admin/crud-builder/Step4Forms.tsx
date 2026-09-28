"use client";

import {
  FieldErrorText,
  inputClassWithError,
  StepRootError,
  wizardCompactInputClass,
} from "@/app/components/admin/crud-builder/WizardFieldError";
import {
  FORM_INPUT_TYPE_OPTIONS,
  FormInputType,
  FormListFormRow,
} from "@/lib/crud-builder";
import { WizardFieldErrors } from "@/lib/crud-builder-validation";

type Step4FormsProps = {
  rows: FormListFormRow[];
  errors?: WizardFieldErrors;
  onChange: (rows: FormListFormRow[]) => void;
};

export default function Step4Forms({ rows, errors = {}, onChange }: Step4FormsProps) {
  function updateRow(fieldName: string, patch: Partial<FormListFormRow>) {
    onChange(
      rows.map((row) => (row.field_name === fieldName ? { ...row, ...patch } : row))
    );
  }

  return (
    <div className="space-y-5">
      <div>
        <h3 className="text-lg font-semibold text-slate-900">
          Configure form field input type, validation rules and labels
        </h3>
        <p className="text-sm text-slate-500">
          Set how each field appears on create and edit pages.
        </p>
      </div>

      <StepRootError errors={errors} keyName="forms._root" />

      <div className="space-y-4">
        {rows.map((row) => {
          const labelError = errors[`forms.${row.field_name}.form_label`];
          const rulesError = errors[`forms.${row.field_name}.validation_rules_json`];

          return (
          <div
            key={row.field_name}
            className="rounded-lg border border-slate-200 bg-slate-50/60 p-4"
          >
            <div className="mb-3">
              <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                Field Name
              </p>
              <p className="text-sm font-semibold text-slate-800">{row.field_name}</p>
            </div>

            <div className="grid gap-3 md:grid-cols-2">
              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">
                  Input Type
                </label>
                <select
                  className={wizardCompactInputClass}
                  value={row.form_input_type}
                  onChange={(e) =>
                    updateRow(row.field_name, {
                      form_input_type: e.target.value as FormInputType,
                    })
                  }
                >
                  {FORM_INPUT_TYPE_OPTIONS.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                      {opt.label}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">Label</label>
                <input
                  className={inputClassWithError(wizardCompactInputClass, labelError)}
                  value={row.form_label}
                  onChange={(e) => updateRow(row.field_name, { form_label: e.target.value })}
                  placeholder="Enter label"
                  aria-invalid={Boolean(labelError)}
                />
                <FieldErrorText message={labelError} />
              </div>

              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">
                  Placeholder
                </label>
                <input
                  className={wizardCompactInputClass}
                  value={row.form_placeholder}
                  onChange={(e) =>
                    updateRow(row.field_name, { form_placeholder: e.target.value })
                  }
                  placeholder="Enter placeholder"
                />
              </div>

              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">
                  Validation Rules (JSON)
                </label>
                <input
                  className={inputClassWithError(wizardCompactInputClass, rulesError)}
                  value={row.validation_rules_json}
                  onChange={(e) =>
                    updateRow(row.field_name, { validation_rules_json: e.target.value })
                  }
                  placeholder='["required","string","max:255"]'
                  aria-invalid={Boolean(rulesError)}
                />
                <FieldErrorText message={rulesError} />
              </div>
            </div>

            <label className="mt-3 flex items-center gap-2 text-sm text-slate-700">
              <input
                type="checkbox"
                checked={row.is_required}
                onChange={(e) => updateRow(row.field_name, { is_required: e.target.checked })}
                className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
              />
              Required
            </label>
          </div>
          );
        })}
      </div>
    </div>
  );
}
