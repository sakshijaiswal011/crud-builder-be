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
  const [search, setSearch] = useState("");
  const [deletingId, setDeletingId] = useState<string | number | null>(null);

  const basePath = useMemo(() => `/admin/module/${slug}`, [slug]);
  const searchable = listColumns.some((column) => column.search_enabled);

  const loadRows = useCallback(async () => {
    if (!apiPrefix) return;
    try {
      setRowsLoading(true);
      setRowsError(null);
      const data = await listRecords(apiPrefix, {
        search: search.trim() || undefined,
        per_page: 50,
      });
      setRows(Array.isArray(data) ? data : []);
    } catch (err) {
      setRows([]);
      setRowsError(err instanceof Error ? err.message : "Failed to load records");
    } finally {
      setRowsLoading(false);
    }
  }, [apiPrefix, search]);

  useEffect(() => {
    if (!loading && module && apiPrefix) {
      loadRows();
    }
  }, [loading, module, apiPrefix, loadRows]);

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

      {searchable ? (
        <div className="flex max-w-md gap-2">
          <input
            type="search"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search…"
            className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
          />
          <button
            type="button"
            onClick={loadRows}
            className="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
          >
            Search
          </button>
        </div>
      ) : null}

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
      />
    </div>
  );
}
