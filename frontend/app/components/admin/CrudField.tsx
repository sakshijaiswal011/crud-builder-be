"use client";

import { CrudFormListMeta } from "@/lib/api";

type CrudFieldProps = {
  config: CrudFormListMeta;
  value: string;
  onChange: (fieldName: string, value: string) => void;
  disabled?: boolean;
};

export default function CrudField({
  config,
  value,
  onChange,
  disabled = false,
}: CrudFieldProps) {
  const fieldName = config.field?.field_name;
  if (!fieldName) return null;

  const inputType = config.form_input_type || "text";
  const label = config.form_label || fieldName;
  const placeholder = config.form_placeholder || "";

  const commonClass =
    "mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 disabled:bg-slate-50";

  return (
    <div className="space-y-1">
      <label htmlFor={fieldName} className="block text-sm font-medium text-slate-700">
        {label}
        {config.is_required ? <span className="text-rose-500"> *</span> : null}
      </label>

      {inputType === "select" ? (
        <select
          id={fieldName}
          name={fieldName}
          value={value}
          disabled={disabled}
          onChange={(e) => onChange(fieldName, e.target.value)}
          className={commonClass}
          required={config.is_required}
        >
          <option value="">{placeholder || `Select ${label}`}</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      ) : (
        <input
          id={fieldName}
          name={fieldName}
          type={inputType === "number" ? "number" : "text"}
          value={value}
          disabled={disabled}
          placeholder={placeholder}
          onChange={(e) => onChange(fieldName, e.target.value)}
          className={commonClass}
          required={config.is_required}
        />
      )}
    </div>
  );
}
