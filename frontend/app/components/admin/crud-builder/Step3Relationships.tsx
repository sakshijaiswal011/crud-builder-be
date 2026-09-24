"use client";

import { useEffect, useState } from "react";
import {
  createEmptyRelationship,
  RELATION_TYPE_OPTIONS,
  RelationshipFormRow,
  RelationType,
} from "@/lib/crud-builder";
import { CrudModule, getCrudModules } from "@/lib/api";

type Step3RelationshipsProps = {
  moduleName: string;
  currentSlug: string;
  relationships: RelationshipFormRow[];
  onChange: (relationships: RelationshipFormRow[]) => void;
};

const inputClass =
  "w-full rounded-md border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500";

export default function Step3Relationships({
  moduleName,
  currentSlug,
  relationships,
  onChange,
}: Step3RelationshipsProps) {
  const [modules, setModules] = useState<CrudModule[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    getCrudModules()
      .then((data) => {
        if (!cancelled) {
          setModules(data.filter((m) => m.slug !== currentSlug));
        }
      })
      .catch(() => {
        if (!cancelled) setModules([]);
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });
    return () => {
      cancelled = true;
    };
  }, [currentSlug]);

  function updateRow(id: string, patch: Partial<RelationshipFormRow>) {
    onChange(
      relationships.map((row) => (row.id === id ? { ...row, ...patch, local_key: "id" } : row))
    );
  }

  function removeRow(id: string) {
    onChange(relationships.filter((row) => row.id !== id));
  }

  const displayName = moduleName.trim() || "this module";

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h3 className="text-lg font-semibold text-slate-900">
            Optional relationships for {displayName}
          </h3>
          <p className="text-sm text-slate-500">
            Link this module to other created modules. You can skip this step if not needed.
          </p>
        </div>
        <button
          type="button"
          onClick={() => onChange([...relationships, createEmptyRelationship()])}
          className="rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-500"
        >
          + Add Relation
        </button>
      </div>

      {relationships.length === 0 ? (
        <div className="rounded-md border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
          No relationships added. Click &quot;Add Relation&quot; or continue to the next step.
        </div>
      ) : null}

      <div className="space-y-4">
        {relationships.map((row, index) => (
          <div key={row.id} className="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
            <div className="mb-3 flex items-center justify-between">
              <p className="text-sm font-semibold text-slate-800">Relation #{index + 1}</p>
              <button
                type="button"
                onClick={() => removeRow(row.id)}
                className="text-xs font-medium text-rose-600 hover:underline"
              >
                Remove
              </button>
            </div>

            <div className="grid gap-3 md:grid-cols-2">
              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">
                  Relation Type
                </label>
                <select
                  className={inputClass}
                  value={row.relation_type}
                  onChange={(e) =>
                    updateRow(row.id, { relation_type: e.target.value as RelationType })
                  }
                >
                  {RELATION_TYPE_OPTIONS.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                      {opt.label}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">
                  Related Module
                </label>
                <select
                  className={inputClass}
                  value={row.related_module_id}
                  onChange={(e) => updateRow(row.id, { related_module_id: e.target.value })}
                  disabled={loading}
                >
                  <option value="">Select module</option>
                  {modules.map((mod) => (
                    <option key={mod.id} value={mod.id}>
                      {mod.menu_name || mod.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">
                  Foreign Key
                </label>
                <input
                  className={inputClass}
                  value={row.foreign_key}
                  onChange={(e) => updateRow(row.id, { foreign_key: e.target.value })}
                  placeholder="Enter foreign key"
                />
              </div>

              <div>
                <label className="mb-1 block text-xs font-medium text-slate-600">
                  Local Key
                </label>
                <input className={inputClass} value="id" readOnly disabled />
                <p className="mt-1 text-xs text-slate-400">Fixed to selected module id</p>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
