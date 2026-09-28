"use client";

import {
  FieldErrorText,
  inputClassWithError,
} from "@/app/components/admin/crud-builder/WizardFieldError";
import { useEffect, useState } from "react";
import { CrudFormListMeta } from "@/lib/api";
import { listRecords } from "@/lib/crud-api";

type CrudFieldProps = {
  config: CrudFormListMeta;
  relationship?: any;
  value: string;
  onChange: (fieldName: string, value: string) => void;
  disabled?: boolean;
  error?: string;
};

export default function CrudField({
  config,
  relationship,
  value,
  onChange,
  disabled = false,
  error,
}: CrudFieldProps) {
  const [options, setOptions] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    const related = relationship?.related_module || relationship?.relatedModule;
    if (relationship && related) {
      setLoading(true);
      const apiVersion = related.api_version || "v1";
      const prefix = `${apiVersion}/${related.api_prefix || related.slug}`;
      listRecords(prefix)
        .then((data) => {
          setOptions(data || []);
        })
        .catch(() => setOptions([]))
        .finally(() => setLoading(false));
    }
  }, [relationship]);

  const fieldName = config.field?.field_name;
  if (!fieldName) return null;

  const inputType = relationship ? "select" : (config.form_input_type || "text");
  const label = relationship?.display_name || config.form_label || fieldName;
  const placeholder = relationship?.display_name 
    ? `Enter ${relationship.display_name.toLowerCase()}` 
    : (config.form_placeholder || "");

  const commonClass = inputClassWithError(
    "mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 disabled:bg-slate-50",
    error
  );

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
          disabled={disabled || loading}
          onChange={(e) => onChange(fieldName, e.target.value)}
          className={commonClass}
          aria-invalid={Boolean(error)}
        >
          <option value="">{loading ? "Loading..." : placeholder || `Select ${label}`}</option>
          {relationship ? (
            options.map((opt) => (
              <option key={opt.id} value={opt.id}>
                {opt[relationship.display_field || "name"] || opt.id}
              </option>
            ))
          ) : (
            <>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </>
          )}
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
          aria-invalid={Boolean(error)}
        />
      )}
      <FieldErrorText message={error} />
    </div>
  );
}
