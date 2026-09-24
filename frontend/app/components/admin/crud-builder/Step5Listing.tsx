"use client";

import { FormListFormRow } from "@/lib/crud-builder";

type Step5ListingProps = {
  rows: FormListFormRow[];
  onChange: (rows: FormListFormRow[]) => void;
};

const inputClass =
  "w-full rounded-md border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500";

export default function Step5Listing({ rows, onChange }: Step5ListingProps) {
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
          Configure list page columns, search, filter, and sort behavior.
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
                Column Heading
              </p>
              <p className="text-sm font-semibold text-slate-800">{row.field_name}</p>
            </div>

            <div className="grid gap-3 md:grid-cols-2">
              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">Label</label>
                <input
                  className={inputClass}
                  value={row.list_label}
                  onChange={(e) => updateRow(row.field_name, { list_label: e.target.value })}
                  placeholder="Enter list label"
                />
              </div>

              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">Width</label>
                <input
                  type="number"
                  min={1}
                  max={100}
                  className={inputClass}
                  value={row.width}
                  onChange={(e) => updateRow(row.field_name, { width: e.target.value })}
                  placeholder="Enter width"
                />
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
        ))}
      </div>
    </div>
  );
}
