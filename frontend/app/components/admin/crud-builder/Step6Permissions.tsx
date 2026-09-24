"use client";

import { PermissionFormRow } from "@/lib/crud-builder";

type Step6PermissionsProps = {
  permissions: PermissionFormRow[];
  onChange: (permissions: PermissionFormRow[]) => void;
};

const inputClass =
  "w-full rounded-md border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500";

export default function Step6Permissions({ permissions, onChange }: Step6PermissionsProps) {
  function updateRow(id: string, patch: Partial<PermissionFormRow>) {
    onChange(permissions.map((row) => (row.id === id ? { ...row, ...patch } : row)));
  }

  function removeRow(id: string) {
    onChange(permissions.filter((row) => row.id !== id));
  }

  return (
    <div className="space-y-5">
      <div>
        <h3 className="text-lg font-semibold text-slate-900">Generate default permissions</h3>
        <p className="text-sm text-slate-500">
          Default view, create, update, delete, and export permissions for this module.
        </p>
      </div>

      <div className="space-y-3">
        {permissions.map((row) => (
          <div
            key={row.id}
            className="grid gap-3 rounded-lg border border-slate-200 bg-slate-50/60 p-4 md:grid-cols-[1fr_1fr_auto_auto]"
          >
            <div>
              <label className="mb-1 block text-xs font-medium text-slate-600">
                Permission Name
              </label>
              <input
                className={inputClass}
                value={row.permission_name}
                onChange={(e) => updateRow(row.id, { permission_name: e.target.value })}
                placeholder="Enter permission name"
              />
            </div>

            <div>
              <label className="mb-1 block text-xs font-medium text-slate-600">Action</label>
              <input
                className={inputClass}
                value={row.action}
                onChange={(e) => updateRow(row.id, { action: e.target.value })}
                placeholder="Enter action"
              />
            </div>

            <label className="flex items-end gap-2 pb-2 text-sm text-slate-700">
              <input
                type="checkbox"
                checked={row.enabled}
                onChange={(e) => updateRow(row.id, { enabled: e.target.checked })}
                className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
              />
              Enabled
            </label>

            <button
              type="button"
              onClick={() => removeRow(row.id)}
              className="self-end pb-2 text-xs font-medium text-rose-600 hover:underline"
            >
              Remove
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}
