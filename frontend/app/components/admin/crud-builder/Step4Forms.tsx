"use client";

import {
  FORM_INPUT_TYPE_OPTIONS,
  FormInputType,
  FormListFormRow,
} from "@/lib/crud-builder";

type Step4FormsProps = {
  rows: FormListFormRow[];
  onChange: (rows: FormListFormRow[]) => void;
};

const inputClass =
  "w-full rounded-md border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500";

export default function Step4Forms({ rows, onChange }: Step4FormsProps) {
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

      <div className="space-y-4">
        {rows.map((row) => (
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
                  className={inputClass}
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
                  className={inputClass}
                  value={row.form_label}
                  onChange={(e) => updateRow(row.field_name, { form_label: e.target.value })}
                  placeholder="Enter label"
                />
              </div>

              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">
                  Placeholder
                </label>
                <input
                  className={inputClass}
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
                  className={inputClass}
                  value={row.validation_rules_json}
                  onChange={(e) =>
                    updateRow(row.field_name, { validation_rules_json: e.target.value })
                  }
                  placeholder='["required","string","max:255"]'
                />
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
        ))}
      </div>
    </div>
  );
}
