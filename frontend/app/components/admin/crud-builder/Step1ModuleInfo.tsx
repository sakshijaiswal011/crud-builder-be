"use client";

import {
  FieldErrorText,
  inputClassWithError,
  wizardInputClass,
} from "@/app/components/admin/crud-builder/WizardFieldError";
import {
  ModuleInfoForm,
  ModuleStatus,
  slugify,
  toTableName,
} from "@/lib/crud-builder";
import { WizardFieldErrors } from "@/lib/crud-builder-validation";

type Step1ModuleInfoProps = {
  value: ModuleInfoForm;
  errors?: WizardFieldErrors;
  onChange: (next: ModuleInfoForm) => void;
  lockIdentity?: boolean;
};

const labelClass = "block text-sm font-medium text-slate-700";

export default function Step1ModuleInfo({
  value,
  errors = {},
  onChange,
  lockIdentity = false,
}: Step1ModuleInfoProps) {
  function update<K extends keyof ModuleInfoForm>(key: K, next: ModuleInfoForm[K]) {
    onChange({ ...value, [key]: next });
  }

  function handleNameChange(name: string) {
    const slug = slugify(name);
    const domain = name.split(/[^a-zA-Z0-9]+/).map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join('');
    
    onChange({
      ...value,
      name,
      slug,
      table_name: value.table_name || toTableName(name),
      api_prefix: value.api_prefix || slug,
      menu_name: value.menu_name || name,
      domain_folder: value.domain_folder || domain,
    });
  }

  async function handleIconUpload(file: File | null) {
    if (!file) {
      update("menu_icon", "");
      update("menu_icon_file_name", "");
      return;
    }

    if (file.type !== "image/svg+xml" && !file.name.toLowerCase().endsWith(".svg")) {
      alert("Please upload an SVG icon file.");
      return;
    }

    const text = await file.text();
    onChange({
      ...value,
      menu_icon: text,
      menu_icon_file_name: file.name,
    });
  }

  return (
    <div className="space-y-5">
      <div>
        <h3 className="text-lg font-semibold text-slate-900">Module Information</h3>
        <p className="text-sm text-slate-500">
          Define the module identity, menu, and basic options.
        </p>
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <div>
          <label className={labelClass} htmlFor="name">
            Name
          </label>
          <input
            id="name"
            className={inputClassWithError(wizardInputClass, errors.name)}
            value={value.name}
            onChange={(e) => handleNameChange(e.target.value)}
            placeholder="Enter name"
            aria-invalid={Boolean(errors.name)}
          />
          <FieldErrorText message={errors.name} />
        </div>

        <div>
          <label className={labelClass} htmlFor="slug">
            Slug
          </label>
          <input
            id="slug"
            className={inputClassWithError(wizardInputClass, errors.slug)}
            value={value.slug}
            onChange={(e) => update("slug", slugify(e.target.value))}
            placeholder="Enter slug"
            aria-invalid={Boolean(errors.slug)}
            disabled={lockIdentity}
          />
          <FieldErrorText message={errors.slug} />
        </div>

        <div>
          <label className={labelClass} htmlFor="table_name">
            Table Name
          </label>
          <input
            id="table_name"
            className={inputClassWithError(wizardInputClass, errors.table_name)}
            value={value.table_name}
            onChange={(e) => update("table_name", toTableName(e.target.value))}
            placeholder="Enter table name"
            aria-invalid={Boolean(errors.table_name)}
            disabled={lockIdentity}
          />
          <FieldErrorText message={errors.table_name} />
        </div>

        <div>
          <label className={labelClass} htmlFor="api_version">
            API Version Prefix
          </label>
          <input
            id="api_version"
            className={inputClassWithError(wizardInputClass, errors.api_version)}
            value={value.api_version}
            onChange={(e) => update("api_version", e.target.value)}
            placeholder="v1"
            aria-invalid={Boolean(errors.api_version)}
          />
          <FieldErrorText message={errors.api_version} />
          <p className="mt-1 text-xs text-slate-500">
            Routes are registered under /api/{value.api_version || "v1"}/…
          </p>
        </div>

        <div>
          <label className={labelClass} htmlFor="api_prefix">
            API Resource Path
          </label>
          <input
            id="api_prefix"
            className={wizardInputClass}
            value={value.api_prefix}
            onChange={(e) => update("api_prefix", e.target.value)}
            placeholder="Enter resource path (e.g. countries)"
          />
        </div>

        <div>
          <label className={labelClass} htmlFor="menu_name">
            Menu Name
          </label>
          <input
            id="menu_name"
            className={wizardInputClass}
            value={value.menu_name}
            onChange={(e) => update("menu_name", e.target.value)}
            placeholder="Enter menu name"
          />
        </div>

        <div>
          <label className={labelClass} htmlFor="menu_group">
            Menu Group
          </label>
          <input
            id="menu_group"
            className={wizardInputClass}
            value={value.menu_group}
            onChange={(e) => update("menu_group", e.target.value)}
            placeholder="Enter menu group"
          />
        </div>

        <div className="md:col-span-2">
          <label className={labelClass} htmlFor="menu_icon">
            Menu Icon (SVG upload)
          </label>
          <input
            id="menu_icon"
            type="file"
            accept=".svg,image/svg+xml"
            className="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-emerald-700 hover:file:bg-emerald-100"
            onChange={(e) => handleIconUpload(e.target.files?.[0] ?? null)}
          />
          {value.menu_icon_file_name ? (
            <div className="mt-2 flex items-center gap-3 rounded-md border border-slate-200 bg-slate-50 px-3 py-2">
              <span
                className="flex h-8 w-8 items-center justify-center text-slate-700 [&_svg]:h-5 [&_svg]:w-5"
                dangerouslySetInnerHTML={{ __html: value.menu_icon }}
              />
              <span className="text-sm text-slate-600">{value.menu_icon_file_name}</span>
              <button
                type="button"
                className="ml-auto text-xs font-medium text-rose-600 hover:underline"
                onClick={() => handleIconUpload(null)}
              >
                Remove
              </button>
            </div>
          ) : (
            <p className="mt-1 text-xs text-slate-400">Upload an .svg icon for the sidebar menu.</p>
          )}
        </div>

        <div>
          <label className={labelClass} htmlFor="status">
            Status
          </label>
          <select
            id="status"
            className={wizardInputClass}
            value={value.status}
            onChange={(e) => update("status", e.target.value as ModuleStatus)}
          >
            <option value="draft">draft</option>
            <option value="active">active</option>
            <option value="inactive">inactive</option>
          </select>
        </div>

        <div>
          <label className={labelClass} htmlFor="target_project_path">
            Target Project Path
          </label>
          <input
            id="target_project_path"
            className={wizardInputClass}
            value={value.target_project_path}
            onChange={(e) => update("target_project_path", e.target.value)}
            placeholder="e.g. C:\xampp\htdocs\my_other_project"
          />
          <p className="mt-1 text-xs text-slate-500">Leave blank to generate in current project.</p>
        </div>

        <div>
          <label className={labelClass} htmlFor="domain_folder">
            Module Folder Name
          </label>
          <input
            id="domain_folder"
            className={wizardInputClass}
            value={value.domain_folder}
            onChange={(e) => update("domain_folder", e.target.value)}
            placeholder="e.g. Posts"
          />
          <p className="mt-1 text-xs text-slate-500">Generates into app/Modules/{value.domain_folder || "..."}/</p>
        </div>

        <div className="flex items-end gap-6 pb-2">
          <label className="flex items-center gap-2 text-sm text-slate-700">
            <input
              type="checkbox"
              checked={value.soft_delete}
              onChange={(e) => update("soft_delete", e.target.checked)}
              className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
            />
            Soft Delete
          </label>
          <label className="flex items-center gap-2 text-sm text-slate-700">
            <input
              type="checkbox"
              checked={value.audit_log}
              onChange={(e) => update("audit_log", e.target.checked)}
              className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
            />
            Audit Log
          </label>
        </div>
      </div>
    </div>
  );
}
