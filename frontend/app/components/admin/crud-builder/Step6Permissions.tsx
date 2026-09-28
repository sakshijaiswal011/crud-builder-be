"use client";

import {
  FieldErrorText,
  inputClassWithError,
  StepRootError,
  wizardCompactInputClass,
} from "@/app/components/admin/crud-builder/WizardFieldError";
import { PermissionFormRow } from "@/lib/crud-builder";
import { WizardFieldErrors } from "@/lib/crud-builder-validation";

type Step6PermissionsProps = {
  permissions: PermissionFormRow[];
  errors?: WizardFieldErrors;
  onChange: (permissions: PermissionFormRow[]) => void;
};

export default function Step6Permissions({
  permissions,
  errors = {},
  onChange,
}: Step6PermissionsProps) {
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

      <StepRootError errors={errors} keyName="permissions._root" />

      <div className="space-y-3">
        {permissions.map((row) => {
          const nameError = errors[`permissions.${row.id}.permission_name`];
          const actionError = errors[`permissions.${row.id}.action`];

          return (
          <div
            key={row.id}
            className="grid gap-3 rounded-lg border border-slate-200 bg-slate-50/60 p-4 md:grid-cols-[1fr_1fr_auto_auto]"
          >
            <div>
              <label className="mb-1 block text-xs font-medium text-slate-600">
                Permission Name
              </label>
              <input
                className={inputClassWithError(wizardCompactInputClass, nameError)}
                value={row.permission_name}
                onChange={(e) => updateRow(row.id, { permission_name: e.target.value })}
                placeholder="Enter permission name"
                aria-invalid={Boolean(nameError)}
              />
              <FieldErrorText message={nameError} />
            </div>

            <div>
              <label className="mb-1 block text-xs font-medium text-slate-600">Action</label>
              <input
                className={inputClassWithError(wizardCompactInputClass, actionError)}
                value={row.action}
                onChange={(e) => updateRow(row.id, { action: e.target.value })}
                placeholder="Enter action"
                aria-invalid={Boolean(actionError)}
              />
              <FieldErrorText message={actionError} />
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
          );
        })}
      </div>
    </div>
  );
}
