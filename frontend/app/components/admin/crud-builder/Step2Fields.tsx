"use client";

import {
  createEmptyField,
  FIELD_TYPE_OPTIONS,
  FieldFormRow,
  FieldType,
} from "@/lib/crud-builder";

type Step2FieldsProps = {
  fields: FieldFormRow[];
  onChange: (fields: FieldFormRow[]) => void;
};

const inputClass =
  "w-full rounded-md border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500";

export default function Step2Fields({ fields, onChange }: Step2FieldsProps) {
  function updateRow(id: string, patch: Partial<FieldFormRow>) {
    onChange(fields.map((field) => (field.id === id ? { ...field, ...patch } : field)));
  }

  function removeRow(id: string) {
    if (fields.length === 1) {
      onChange([createEmptyField()]);
      return;
    }
    onChange(fields.filter((field) => field.id !== id));
  }

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h3 className="text-lg font-semibold text-slate-900">Database Fields</h3>
          <p className="text-sm text-slate-500">
            Add columns for the module table. These become field records after save.
          </p>
        </div>
        <button
          type="button"
          onClick={() => onChange([...fields, createEmptyField()])}
          className="rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-500"
        >
          + Add Field
        </button>
      </div>

      <div className="space-y-4">
        {fields.map((field, index) => (
          <div
            key={field.id}
            className="rounded-lg border border-slate-200 bg-slate-50/60 p-4"
          >
            <div className="mb-3 flex items-center justify-between">
              <p className="text-sm font-semibold text-slate-800">
                Field #{index + 1}
                {field.field_name ? (
                  <span className="ml-2 font-normal text-slate-500">
                    ({field.field_name})
                  </span>
                ) : null}
              </p>
              <button
                type="button"
                onClick={() => removeRow(field.id)}
                className="text-xs font-medium text-rose-600 hover:underline"
              >
                Remove
              </button>
            </div>

            <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">
                  Field Name
                </label>
                <input
                  className={inputClass}
                  value={field.field_name}
                  onChange={(e) =>
                    updateRow(field.id, {
                      field_name: e.target.value
                        .toLowerCase()
                        .replace(/[^a-z0-9_]/g, "_"),
                    })
                  }
                  placeholder="Enter field name"
                  required
                />
              </div>

              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">
                  Type
                </label>
                <select
                  className={inputClass}
                  value={field.type}
                  onChange={(e) =>
                    updateRow(field.id, { type: e.target.value as FieldType })
                  }
                >
                  {FIELD_TYPE_OPTIONS.map((option) => (
                    <option key={option.value} value={option.value}>
                      {option.label}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">
                  Length
                </label>
                <input
                  className={inputClass}
                  value={field.length}
                  onChange={(e) => updateRow(field.id, { length: e.target.value })}
                  placeholder="Enter length"
                />
              </div>

              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">
                  Default Value
                </label>
                <input
                  className={inputClass}
                  value={field.default_value}
                  onChange={(e) =>
                    updateRow(field.id, { default_value: e.target.value })
                  }
                  placeholder="Enter default value"
                />
              </div>

              <div className="md:col-span-2 xl:col-span-4">
                <label className="mb-1 block text-xs font-medium text-slate-600">
                  Comment
                </label>
                <input
                  className={inputClass}
                  value={field.comment}
                  onChange={(e) => updateRow(field.id, { comment: e.target.value })}
                  placeholder="Enter comment"
                />
              </div>
            </div>

            <div className="mt-3 flex flex-wrap gap-5">
              <label className="flex items-center gap-2 text-sm text-slate-700">
                <input
                  type="checkbox"
                  checked={field.nullable}
                  onChange={(e) => updateRow(field.id, { nullable: e.target.checked })}
                  className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                />
                Nullable
              </label>
              <label className="flex items-center gap-2 text-sm text-slate-700">
                <input
                  type="checkbox"
                  checked={field.is_unique}
                  onChange={(e) => updateRow(field.id, { is_unique: e.target.checked })}
                  className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                />
                Is Unique
              </label>
              <label className="flex items-center gap-2 text-sm text-slate-700">
                <input
                  type="checkbox"
                  checked={field.is_indexed}
                  onChange={(e) =>
                    updateRow(field.id, { is_indexed: e.target.checked })
                  }
                  className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                />
                Is Indexed
              </label>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
