"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { CrudModule, getCrudModules, getModuleHref, getModuleLabel } from "@/lib/api";

export default function AdminPage() {

  const [modules, setModules] = useState<CrudModule[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 8;

  const [isDeleting, setIsDeleting] = useState<number | null>(null);
  const [deleteError, setDeleteError] = useState<{id: number, message: string} | null>(null);
  
  // Custom Modal & Toast States
  const [moduleToDelete, setModuleToDelete] = useState<{id: number, name: string} | null>(null);
  const [toastMessage, setToastMessage] = useState<{title: string, type: 'success' | 'error'} | null>(null);

  // Auto-hide toast
  useEffect(() => {
    if (toastMessage) {
      const timer = setTimeout(() => setToastMessage(null), 3000);
      return () => clearTimeout(timer);
    }
  }, [toastMessage]);

  useEffect(() => {
    async function fetchModules() {
      try {
        setLoading(true);
        const data = await getCrudModules();
        setModules(data);
      } catch (err) {
        setError(err instanceof Error ? err.message : "Failed to load modules");
      } finally {
        setLoading(false);
      }
    }
    fetchModules();
  }, []);

  const promptDelete = (id: number, name: string) => {
    setModuleToDelete({ id, name });
    setDeleteError(null);
  };

  const confirmDelete = async () => {
    if (!moduleToDelete) return;
    
    const { id, name } = moduleToDelete;
    setIsDeleting(id);
    setModuleToDelete(null); // close modal immediately
    
    try {
      const { deleteCrudModule } = await import('@/lib/api');
      await deleteCrudModule(id);
      setModules((prev) => prev.filter((m) => m.id !== id));
      setToastMessage({ title: `Module '${name}' deleted successfully.`, type: 'success' });
    } catch (err) {
      setDeleteError({
        id,
        message: err instanceof Error ? err.message : "Failed to delete module",
      });
      setToastMessage({ title: "Failed to delete module.", type: 'error' });
    } finally {
      setIsDeleting(null);
    }
  };

  const totalPages = Math.ceil(modules.length / itemsPerPage);
  const paginatedModules = modules.slice(
    (currentPage - 1) * itemsPerPage,
    currentPage * itemsPerPage
  );

  return (
    <div className="h-full w-full bg-slate-50 flex flex-col p-8">
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p className="mt-1 text-sm text-slate-500">
          Overview of your generated CRUD modules
        </p>
      </div>

      <div className="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div className="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
          <h2 className="font-semibold text-slate-800">Generated Modules</h2>
          <span className="text-sm font-medium text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
            {modules.length} Total
          </span>
        </div>

        <div className="p-6">
          {loading ? (
            <div className="flex h-full items-center justify-center">
              <p className="text-slate-500">Loading modules...</p>
            </div>
          ) : error ? (
            <div className="flex h-full items-center justify-center">
              <p className="text-rose-500">{error}</p>
            </div>
          ) : modules.length === 0 ? (
            <div className="flex h-full flex-col items-center justify-center text-center">
              <div className="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                <span className="text-slate-400 text-xl font-bold">!</span>
              </div>
              <h3 className="text-lg font-medium text-slate-900 mb-1">No modules found</h3>
              <p className="text-sm text-slate-500 mb-6">You haven't generated any modules yet.</p>
              <Link
                href="/admin/crud-builder"
                className="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors"
              >
                Go to CRUD Builder
              </Link>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              {paginatedModules.map((m) => (
                <div
                  key={m.id}
                  className="group relative flex flex-col justify-between bg-white border border-slate-200 rounded-xl p-5 hover:border-indigo-300 hover:shadow-md transition-all"
                >
                  <button
                    onClick={() => promptDelete(m.id, m.name)}
                    disabled={isDeleting === m.id}
                    title="Delete Module"
                    className="absolute top-4 right-4 h-8 w-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-400 opacity-0 group-hover:opacity-100 hover:bg-rose-100 hover:text-rose-600 transition-all disabled:opacity-50"
                  >
                    {isDeleting === m.id ? (
                      <span className="animate-spin text-xs">⏳</span>
                    ) : (
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    )}
                  </button>

                  <div>
                    <div className="flex items-start justify-between mb-4 pr-8">
                      <div className="h-10 w-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-lg">
                        {(m.menu_icon || getModuleLabel(m)).charAt(0).toUpperCase()}
                      </div>
                      <span
                        className={`text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full ${
                          m.status ? "bg-emerald-50 text-emerald-600" : "bg-amber-50 text-amber-600"
                        }`}
                      >
                        {m.status ? "Active" : "Inactive"}
                      </span>
                    </div>
                    
                    <h3 className="text-base font-semibold text-slate-900 mb-1 truncate">
                      {getModuleLabel(m)}
                    </h3>
                    <p className="text-xs text-slate-500 font-mono mb-4 truncate">
                      {m.table_name}
                    </p>
                  </div>

                  {deleteError?.id === m.id && (
                    <div className="mb-4 text-xs font-medium text-rose-600 bg-rose-50 p-2 rounded-lg border border-rose-100">
                      {deleteError.message}
                    </div>
                  )}

                  <div className="flex items-center gap-2 mt-auto pt-4 border-t border-slate-100">
                    <Link
                      href={getModuleHref(m)}
                      className="flex-1 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-medium py-2 rounded-lg text-center transition-colors border border-slate-200"
                    >
                      View Data
                    </Link>
                    <Link
                      href={`/admin/crud-builder?id=${m.id}`}
                      className="flex-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-medium py-2 rounded-lg text-center transition-colors border border-indigo-100"
                    >
                      Edit Schema
                    </Link>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        {totalPages > 1 && (
          <div className="border-t border-slate-200 px-6 py-4 flex items-center justify-between bg-slate-50/50">
            <p className="text-sm text-slate-500">
              Showing <span className="font-medium text-slate-900">{(currentPage - 1) * itemsPerPage + 1}</span> to{" "}
              <span className="font-medium text-slate-900">
                {Math.min(currentPage * itemsPerPage, modules.length)}
              </span>{" "}
              of <span className="font-medium text-slate-900">{modules.length}</span> modules
            </p>
            <div className="flex items-center gap-2">
              <button
                onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                disabled={currentPage === 1}
                className="px-3 py-1.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                Previous
              </button>
              <div className="flex items-center gap-1">
                {Array.from({ length: totalPages }).map((_, i) => (
                  <button
                    key={i}
                    onClick={() => setCurrentPage(i + 1)}
                    className={`w-8 h-8 rounded-lg text-sm font-medium transition-colors flex items-center justify-center ${
                      currentPage === i + 1
                        ? "bg-indigo-600 text-white border-transparent"
                        : "border border-slate-200 text-slate-600 hover:bg-slate-100"
                    }`}
                  >
                    {i + 1}
                  </button>
                ))}
              </div>
              <button
                onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
                disabled={currentPage === totalPages}
                className="px-3 py-1.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                Next
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Delete Confirmation Modal */}
      {moduleToDelete && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div className="p-6">
              <div className="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center mb-4">
                <svg className="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <h3 className="text-lg font-bold text-slate-900 mb-2">Delete Module?</h3>
              <p className="text-sm text-slate-500 mb-6">
                Are you sure you want to permanently delete the <strong>{moduleToDelete.name}</strong> module? This will drop the database table and erase all generated files. This action cannot be undone.
              </p>
              <div className="flex items-center gap-3">
                <button
                  onClick={() => setModuleToDelete(null)}
                  className="flex-1 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition-colors"
                >
                  Cancel
                </button>
                <button
                  onClick={confirmDelete}
                  className="flex-1 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-medium rounded-lg transition-colors shadow-sm shadow-rose-200"
                >
                  Yes, Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Success/Error Toast */}
      {toastMessage && (
        <div className="fixed bottom-6 right-6 z-50 animate-in slide-in-from-bottom-5 fade-in duration-300">
          <div className={`px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 border ${
            toastMessage.type === 'success' 
              ? 'bg-emerald-50 border-emerald-200 text-emerald-800' 
              : 'bg-rose-50 border-rose-200 text-rose-800'
          }`}>
            <div className={`flex items-center justify-center w-6 h-6 rounded-full ${
              toastMessage.type === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'
            }`}>
              {toastMessage.type === 'success' ? (
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" /></svg>
              ) : (
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M6 18L18 6M6 6l12 12" /></svg>
              )}
            </div>
            <p className="text-sm font-medium">{toastMessage.title}</p>
          </div>
        </div>
      )}
    </div>
  );
}
