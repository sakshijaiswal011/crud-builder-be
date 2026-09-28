"use client";

import Link from "next/link";
import { useParams } from "next/navigation";
import { useCallback, useEffect, useMemo, useState } from "react";
import CrudTable from "@/app/components/admin/CrudTable";
import { CrudRecord, deleteRecord, listRecords } from "@/lib/crud-api";
import { useModule } from "@/lib/useModule";

export default function ModuleListPage() {
  const params = useParams<{ slug: string }>();
  const slug = params.slug;
  const { module, loading, error, label, apiPrefix, listColumns } = useModule(slug);

  const [rows, setRows] = useState<CrudRecord[]>([]);
  const [rowsLoading, setRowsLoading] = useState(true);
  const [rowsError, setRowsError] = useState<string | null>(null);
  const [fieldSearch, setFieldSearch] = useState<Record<string, string>>({});
  const [sortBy, setSortBy] = useState<string | null>(null);
  const [sortDir, setSortDir] = useState<"asc" | "desc">("asc");
  const [deletingId, setDeletingId] = useState<string | number | null>(null);

  const basePath = useMemo(() => `/admin/module/${slug}`, [slug]);

  const loadRows = useCallback(async () => {
    if (!apiPrefix) return;
    try {
      setRowsLoading(true);
      setRowsError(null);
      const data = await listRecords(apiPrefix, {
        search: fieldSearch,
        sort_by: sortBy ?? undefined,
        sort_dir: sortDir,
        per_page: 50,
      });
      setRows(Array.isArray(data) ? data : []);
    } catch (err) {
      setRows([]);
      setRowsError(err instanceof Error ? err.message : "Failed to load records");
    } finally {
      setRowsLoading(false);
    }
  }, [apiPrefix, fieldSearch, sortBy, sortDir]);

  useEffect(() => {
    if (!loading && module && apiPrefix) {
      const timer = window.setTimeout(() => {
        loadRows();
      }, 350);

      return () => window.clearTimeout(timer);
    }
  }, [loading, module, apiPrefix, loadRows]);

  function handleFieldSearchChange(fieldName: string, value: string) {
    setFieldSearch((prev) => ({ ...prev, [fieldName]: value }));
  }

  function handleSort(fieldName: string) {
    const column = listColumns.find((c) => c.field?.field_name === fieldName);
    if (!column?.sorting_enabled) return;

    if (sortBy !== fieldName) {
      setSortBy(fieldName);
      setSortDir("asc");
      return;
    }

    setSortDir((prev) => (prev === "asc" ? "desc" : "asc"));
  }

  async function handleDelete(row: CrudRecord) {
    if (row.id === undefined || row.id === null) return;
    const confirmed = window.confirm("Delete this record?");
    if (!confirmed) return;

    try {
      setDeletingId(row.id);
      await deleteRecord(apiPrefix, row.id);
      await loadRows();
    } catch (err) {
      alert(err instanceof Error ? err.message : "Delete failed");
    } finally {
      setDeletingId(null);
    }
  }

  if (loading) {
    return <p className="text-sm text-slate-500">Loading module…</p>;
  }

  if (error || !module) {
    return <p className="text-sm text-rose-600">{error ?? "Module not found."}</p>;
  }

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 className="text-xl font-semibold text-slate-900">{label}</h2>
          <p className="text-sm text-slate-500">Manage {label.toLowerCase()} records</p>
        </div>
        <Link
          href={`${basePath}/create`}
          className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
        >
          Add New
        </Link>
      </div>

      {rowsError ? (
        <div className="rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
          {rowsError}
          <p className="mt-1 text-xs">
            Make sure the generated migration was run (`php artisan migrate`) and the
            module API route exists.
          </p>
        </div>
      ) : null}

      <CrudTable
        columns={listColumns}
        rows={rows}
        basePath={basePath}
        loading={rowsLoading}
        onDelete={handleDelete}
        deletingId={deletingId}
        fieldSearch={fieldSearch}
        onFieldSearchChange={handleFieldSearchChange}
        sortBy={sortBy}
        sortDir={sortDir}
        onSort={handleSort}
      />
    </div>
  );
}
