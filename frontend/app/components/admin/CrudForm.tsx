"use client";

import { FormEvent, useEffect, useMemo, useState } from "react";
import { CrudFormListMeta } from "@/lib/api";
import { CrudApiError, CrudFieldErrors, CrudRecord } from "@/lib/crud-api";
import CrudField from "./CrudField";

type CrudFormProps = {
  fields: CrudFormListMeta[];
  relationships?: any[];
  initialValues?: CrudRecord | null;
  submitLabel?: string;
  onSubmit: (values: Record<string, unknown>) => Promise<void> | void;
  onCancel?: () => void;
};

export default function CrudForm({
  fields,
  relationships = [],
  initialValues = null,
  submitLabel = "Save",
  onSubmit,
  onCancel,
}: CrudFormProps) {
  const emptyValues = useMemo(() => {
    const values: Record<string, string> = {};
    for (const field of fields) {
      const name = field.field?.field_name;
      if (!name) continue;
      values[name] = "";
    }
    return values;
  }, [fields]);

  const [values, setValues] = useState<Record<string, string>>(emptyValues);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<CrudFieldErrors>({});

  useEffect(() => {
    const next = { ...emptyValues };
    if (initialValues) {
      for (const key of Object.keys(next)) {
        const raw = initialValues[key];
        next[key] = raw === null || raw === undefined ? "" : String(raw);
      }
    }
    setValues(next);
  }, [emptyValues, initialValues]);

  function handleChange(fieldName: string, value: string) {
    setValues((prev) => ({ ...prev, [fieldName]: value }));
    setFieldErrors((prev) => {
      if (!prev[fieldName]) return prev;
      const next = { ...prev };
      delete next[fieldName];
      return next;
    });
    setError(null);
  }

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setSaving(true);
    setError(null);
    setFieldErrors({});

    try {
      const payload: Record<string, unknown> = {};
      for (const field of fields) {
        const name = field.field?.field_name;
        if (!name) continue;
        const value = values[name] ?? "";
        if (field.form_input_type === "number" && value !== "") {
          payload[name] = Number(value);
        } else {
          payload[name] = value;
        }
      }
      await onSubmit(payload);
    } catch (err) {
      if (err instanceof CrudApiError) {
        setFieldErrors(err.fieldErrors);
        setError(
          Object.keys(err.fieldErrors).length > 0
            ? "Please fix the errors below."
            : err.message
        );
      } else {
        setError(err instanceof Error ? err.message : "Failed to save");
      }
    } finally {
      setSaving(false);
    }
  }

  return (
    <form noValidate onSubmit={handleSubmit} className="mx-auto max-w-2xl space-y-4">
      {error ? (
        <div className="rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
          {error}
        </div>
      ) : null}

      {fields.map((field) => {
        const name = field.field?.field_name;
        if (!name) return null;
        
        const relationship = relationships.find((rel) => rel.foreign_key === name);

        return (
          <CrudField
            key={field.id}
            config={field}
            relationship={relationship}
            value={values[name] ?? ""}
            onChange={handleChange}
            disabled={saving}
            error={fieldErrors[name]}
          />
        );
      })}

      <div className="flex items-center gap-3 pt-2">
        <button
          type="submit"
          disabled={saving}
          className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-60"
        >
          {saving ? "Saving…" : submitLabel}
        </button>
        {onCancel ? (
          <button
            type="button"
            onClick={onCancel}
            disabled={saving}
            className="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
          >
            Cancel
          </button>
        ) : null}
      </div>
    </form>
  );
}
