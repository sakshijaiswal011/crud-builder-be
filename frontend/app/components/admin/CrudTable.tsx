"use client";

import Link from "next/link";
import { CrudFormListMeta } from "@/lib/api";
import { CrudRecord } from "@/lib/crud-api";

type CrudTableProps = {
  columns: CrudFormListMeta[];
  rows: CrudRecord[];
  basePath: string;
  loading?: boolean;
  onDelete?: (row: CrudRecord) => void;
  deletingId?: string | number | null;
};

function cellValue(row: CrudRecord, fieldName: string) {
  const value = row[fieldName];
  if (value === null || value === undefined || value === "") return "—";
  return String(value);
}

export default function CrudTable({
  columns,
  rows,
  basePath,
  loading = false,
  onDelete,
  deletingId = null,
}: CrudTableProps) {
  if (loading) {
    return <p className="text-sm text-slate-500">Loading records…</p>;
  }

  if (!rows.length) {
    return (
      <div className="rounded-md border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
        No records found.
      </div>
    );
  }

  return (
    <div className="overflow-x-auto rounded-md border border-slate-200">
      <table className="min-w-full divide-y divide-slate-200 text-sm">
        <thead className="bg-slate-50">
          <tr>
            <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
              ID
            </th>
            {columns.map((column) => {
              const name = column.field?.field_name;
              if (!name) return null;
              return (
                <th
                  key={column.id}
                  className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                  style={{ width: column.width ? `${column.width}%` : undefined }}
                >
                  {column.list_label || column.form_label || name}
                </th>
              );
            })}
            <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
              Actions
            </th>
          </tr>
        </thead>
        <tbody className="divide-y divide-slate-100 bg-white">
          {rows.map((row) => {
            const id = row.id;
            return (
              <tr key={String(id)} className="hover:bg-slate-50/80">
                <td className="px-4 py-3 text-slate-700">{String(id ?? "—")}</td>
                {columns.map((column) => {
                  const name = column.field?.field_name;
                  if (!name) return null;
                  return (
                    <td key={`${id}-${name}`} className="px-4 py-3 text-slate-800">
                      {cellValue(row, name)}
                    </td>
                  );
                })}
                <td className="px-4 py-3">
                  <div className="flex items-center justify-end gap-2">
                    {id !== undefined && id !== null ? (
                      <>
                        <Link
                          href={`${basePath}/${id}/edit`}
                          className="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50"
                        >
                          Edit
                        </Link>
                        {onDelete ? (
                          <button
                            type="button"
                            onClick={() => onDelete(row)}
                            disabled={deletingId === id}
                            className="rounded-md border border-rose-200 px-2.5 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50 disabled:opacity-60"
                          >
                            {deletingId === id ? "Deleting…" : "Delete"}
                          </button>
                        ) : null}
                      </>
                    ) : null}
                  </div>
                </td>
              </tr>
            );
          })}
        </tbody>
      </table>
    </div>
  );
}
