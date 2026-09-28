"use client";

import {
  FieldErrorText,
  inputClassWithError,
  wizardCompactInputClass,
} from "@/app/components/admin/crud-builder/WizardFieldError";
import { FormListFormRow, SHOW_STEP5_FILTERABLE } from "@/lib/crud-builder";
import { WizardFieldErrors } from "@/lib/crud-builder-validation";

type Step5ListingProps = {
  rows: FormListFormRow[];
  errors?: WizardFieldErrors;
  onChange: (rows: FormListFormRow[]) => void;
};

export default function Step5Listing({ rows, errors = {}, onChange }: Step5ListingProps) {
  function updateRow(fieldName: string, patch: Partial<FormListFormRow>) {
    onChange(
      rows.map((row) => (row.field_name === fieldName ? { ...row, ...patch } : row))
    );
  }

  return (
    <div className="space-y-5">
      <div>
        <h3 className="text-lg font-semibold text-slate-900">List configuration</h3>
        <p className="text-sm text-slate-500">
          Configure list page columns, search, and sort behavior.
        </p>
      </div>

      <div className="space-y-4">
        {rows.map((row) => {
          const listLabelError = errors[`forms.${row.field_name}.list_label`];
          const widthError = errors[`forms.${row.field_name}.width`];

          return (
          <div
            key={row.field_name}
            className="rounded-lg border border-slate-200 bg-slate-50/60 p-4"
          >
            <div className="mb-3">
              <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                Column Heading
              </p>
              <p className="text-sm font-semibold text-slate-800">{row.field_name}</p>
            </div>

            <div className="grid gap-3 md:grid-cols-2">
              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">Label</label>
                <input
                  className={inputClassWithError(wizardCompactInputClass, listLabelError)}
                  value={row.list_label}
                  onChange={(e) => updateRow(row.field_name, { list_label: e.target.value })}
                  placeholder="Enter list label"
                  aria-invalid={Boolean(listLabelError)}
                />
                <FieldErrorText message={listLabelError} />
              </div>

              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">Width</label>
                <input
                  type="number"
                  min={1}
                  max={100}
                  className={inputClassWithError(wizardCompactInputClass, widthError)}
                  value={row.width}
                  onChange={(e) => updateRow(row.field_name, { width: e.target.value })}
                  placeholder="Enter width"
                  aria-invalid={Boolean(widthError)}
                />
                <FieldErrorText message={widthError} />
              </div>
            </div>

            <div className="mt-3 flex flex-wrap gap-5">
              <label className="flex items-center gap-2 text-sm text-slate-700">
                <input
                  type="checkbox"
                  checked={row.search_enabled}
                  onChange={(e) =>
                    updateRow(row.field_name, { search_enabled: e.target.checked })
                  }
                  className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                />
                Searchable
              </label>
              {SHOW_STEP5_FILTERABLE ? (
                <label className="flex items-center gap-2 text-sm text-slate-700">
                  <input
                    type="checkbox"
                    checked={row.filtering_enabled}
                    onChange={(e) =>
                      updateRow(row.field_name, { filtering_enabled: e.target.checked })
                    }
                    className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                  />
                  Filterable
                </label>
              ) : null}
              <label className="flex items-center gap-2 text-sm text-slate-700">
                <input
                  type="checkbox"
                  checked={row.sorting_enabled}
                  onChange={(e) =>
                    updateRow(row.field_name, { sorting_enabled: e.target.checked })
                  }
                  className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                />
                Sortable
              </label>
            </div>
          </div>
          );
        })}
      </div>
    </div>
  );
}
