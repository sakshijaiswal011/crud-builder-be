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
  fieldSearch?: Record<string, string>;
  onFieldSearchChange?: (fieldName: string, value: string) => void;
  sortBy?: string | null;
  sortDir?: "asc" | "desc";
  onSort?: (fieldName: string) => void;
};

function cellValue(row: CrudRecord, fieldName: string) {
  const value = row[fieldName];
  if (value === null || value === undefined || value === "") return "—";
  return String(value);
}

function SortIcon({
  active,
  direction,
}: {
  active: boolean;
  direction: "asc" | "desc";
}) {
  if (!active) {
    return (
      <span className="ml-1 inline-flex text-slate-400" aria-hidden>
        ↕
      </span>
    );
  }

  return (
    <span className="ml-1 inline-flex text-indigo-600" aria-hidden>
      {direction === "asc" ? "↑" : "↓"}
    </span>
  );
}

const searchInputClass =
  "w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500";

export default function CrudTable({
  columns,
  rows,
  basePath,
  loading = false,
  onDelete,
  deletingId = null,
  fieldSearch = {},
  onFieldSearchChange,
  sortBy = null,
  sortDir = "asc",
  onSort,
}: CrudTableProps) {
  const listColumns = columns.filter((column) => column.field?.field_name !== "id");

  const searchableColumns = listColumns.filter(
    (column) => column.search_enabled && column.field?.field_name && onFieldSearchChange
  );

  return (
    <div className="space-y-3">
      {searchableColumns.length > 0 ? (
        <div className="rounded-md border border-slate-200 bg-slate-50/80 p-4">
          <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            Search
          </p>
          <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {searchableColumns.map((column) => {
              const name = column.field!.field_name;
              const label = column.list_label || column.form_label || name;

              return (
                <div key={`search-${column.id}`}>
                  <label className="mb-1 block text-xs font-medium text-slate-600">{label}</label>
                  <input
                    type="search"
                    value={fieldSearch[name] ?? ""}
                    onChange={(e) => onFieldSearchChange!(name, e.target.value)}
                    placeholder={`Search ${label}`}
                    className={searchInputClass}
                    aria-label={`Search ${label}`}
                  />
                </div>
              );
            })}
          </div>
        </div>
      ) : null}

      {loading ? (
        <p className="text-sm text-slate-500">Loading records…</p>
      ) : (
        <div className="overflow-x-auto rounded-md border border-slate-200">
          <table className="min-w-full divide-y divide-slate-200 text-sm">
            <thead className="bg-slate-50">
              <tr>
                {listColumns.map((column) => {
                  const name = column.field?.field_name;
                  if (!name) return null;
                  const label = column.list_label || column.form_label || name;
                  const sortable = column.sorting_enabled && onSort;

                  return (
                    <th
                      key={column.id}
                      className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                      style={{ width: column.width ? `${column.width}%` : undefined }}
                    >
                      {sortable ? (
                        <button
                          type="button"
                          onClick={() => onSort(name)}
                          className="inline-flex items-center font-semibold uppercase tracking-wide text-slate-600 hover:text-indigo-600"
                          title={`Sort by ${label}`}
                        >
                          {label}
                          <SortIcon active={sortBy === name} direction={sortDir} />
                        </button>
                      ) : (
                        label
                      )}
                    </th>
                  );
                })}
                <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                  Actions
                </th>
              </tr>
            </thead>
        <tbody className="divide-y divide-slate-100 bg-white">
          {!rows.length ? (
            <tr>
              <td
                colSpan={listColumns.length + 1}
                className="px-4 py-8 text-center text-sm text-slate-500"
              >
                No records found.
              </td>
            </tr>
          ) : (
            rows.map((row) => {
              const id = row.id;
              return (
                <tr key={String(id)} className="hover:bg-slate-50/80">
                  {listColumns.map((column) => {
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
            })
          )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
