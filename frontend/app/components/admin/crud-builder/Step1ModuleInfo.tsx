"use client";

import {
  ModuleInfoForm,
  ModuleStatus,
  slugify,
  toTableName,
} from "@/lib/crud-builder";

type Step1ModuleInfoProps = {
  value: ModuleInfoForm;
  onChange: (next: ModuleInfoForm) => void;
};

const inputClass =
  "mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500";

const labelClass = "block text-sm font-medium text-slate-700";

export default function Step1ModuleInfo({ value, onChange }: Step1ModuleInfoProps) {
  function update<K extends keyof ModuleInfoForm>(key: K, next: ModuleInfoForm[K]) {
    onChange({ ...value, [key]: next });
  }

  function handleNameChange(name: string) {
    const slug = slugify(name);
    onChange({
      ...value,
      name,
      slug,
      table_name: value.table_name || toTableName(name),
      api_prefix: value.api_prefix || slug,
      menu_name: value.menu_name || name,
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
            className={inputClass}
            value={value.name}
            onChange={(e) => handleNameChange(e.target.value)}
            placeholder="Enter name"
            required
          />
        </div>

        <div>
          <label className={labelClass} htmlFor="slug">
            Slug
          </label>
          <input
            id="slug"
            className={inputClass}
            value={value.slug}
            onChange={(e) => update("slug", slugify(e.target.value))}
            placeholder="Enter slug"
            required
          />
        </div>

        <div>
          <label className={labelClass} htmlFor="table_name">
            Table Name
          </label>
          <input
            id="table_name"
            className={inputClass}
            value={value.table_name}
            onChange={(e) => update("table_name", toTableName(e.target.value))}
            placeholder="Enter table name"
            required
          />
        </div>

        <div>
          <label className={labelClass} htmlFor="api_prefix">
            API Prefix
          </label>
          <input
            id="api_prefix"
            className={inputClass}
            value={value.api_prefix}
            onChange={(e) => update("api_prefix", e.target.value)}
            placeholder="Enter API prefix"
          />
        </div>

        <div>
          <label className={labelClass} htmlFor="menu_name">
            Menu Name
          </label>
          <input
            id="menu_name"
            className={inputClass}
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
            className={inputClass}
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
            className={inputClass}
            value={value.status}
            onChange={(e) => update("status", e.target.value as ModuleStatus)}
          >
            <option value="draft">draft</option>
            <option value="active">active</option>
            <option value="inactive">inactive</option>
          </select>
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
