"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useMemo, useState } from "react";
import {
  CrudModule,
  getCrudModules,
  getModuleHref,
  getModuleLabel,
} from "@/lib/api";

export default function Sidebar() {
  const pathname = usePathname();
  const isCrudBuilder = pathname.startsWith("/admin/crud-builder");

  const [modules, setModules] = useState<CrudModule[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;

    async function loadModules() {
      try {
        setLoading(true);
        setError(null);
        const data = await getCrudModules();
        if (!cancelled) {
          setModules(data);
        }
      } catch (err) {
        if (!cancelled) {
          setError(err instanceof Error ? err.message : "Failed to load modules");
          setModules([]);
        }
      } finally {
        if (!cancelled) {
          setLoading(false);
        }
      }
    }

    loadModules();

    return () => {
      cancelled = true;
    };
  }, [pathname]);

  const groupedModules = useMemo(() => {
    const groups = new Map<string, CrudModule[]>();

    for (const module of modules) {
      const group = module.menu_group?.trim() || "Modules";
      const list = groups.get(group) ?? [];
      list.push(module);
      groups.set(group, list);
    }

    return Array.from(groups.entries());
  }, [modules]);

  return (
    <aside className="flex h-screen w-64 shrink-0 flex-col bg-black text-slate-100">
      <div className="flex items-center gap-3 border-b border-white/15 px-5 py-5">
        <div
          className="flex h-11 w-11 items-center justify-center rounded-full bg-indigo-500 text-sm font-semibold text-white"
          aria-hidden
        >
          A
        </div>
        <div className="min-w-0">
          <p className="text-sm font-semibold text-white">Admin</p>
          <p className="text-xs text-slate-400">Administrator</p>
        </div>
      </div>
      <nav className="p-3">
        <p className="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
          Main
        </p>
        <Link
          href="/admin"
          className={`flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors ${
            pathname === "/admin"
              ? "bg-indigo-600 text-white"
              : "text-slate-300 hover:bg-white/10 hover:text-white"
          }`}
        >
          <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-white/10 text-[10px] uppercase text-slate-300" aria-hidden>
            D
          </span>
          Dashboard
        </Link>
      </nav>

      <nav className="flex-1 overflow-y-auto p-3">
        <p className="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
          Builder
        </p>
        <Link
          href="/admin/crud-builder"
          className={`mb-4 flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors ${
            isCrudBuilder
              ? "bg-indigo-600 text-white"
              : "text-slate-300 hover:bg-white/10 hover:text-white"
          }`}
        >
          <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-white/10 text-[10px] uppercase text-slate-300" aria-hidden>
            C
          </span>
          CRUD Builder
        </Link>

        <p className="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
          Created Modules
        </p>

        {loading && (
          <p className="px-3 py-2 text-xs text-slate-500">Loading modules…</p>
        )}

        {!loading && error && (
          <p className="px-3 py-2 text-xs text-rose-400">{error}</p>
        )}

        {!loading && !error && modules.length === 0 && (
          <p className="px-3 py-2 text-xs text-slate-500">
            No modules yet. Create one from CRUD Builder.
          </p>
        )}

        {!loading &&
          !error &&
          groupedModules.map(([group, items]) => (
            <div key={group} className="mb-3">
              {groupedModules.length > 1 && (
                <p className="mb-1 px-3 text-[11px] font-medium text-slate-500">
                  {group}
                </p>
              )}
              <ul className="space-y-0.5">
                {items.map((module) => {
                  const href = getModuleHref(module);
                  const active = pathname === href || pathname.startsWith(`${href}/`);
                  const label = getModuleLabel(module);

                  return (
                    <li key={module.id}>
                      <Link
                        href={href}
                        className={`flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors ${
                          active
                            ? "bg-indigo-600 text-white"
                            : "text-slate-300 hover:bg-white/10 hover:text-white"
                        }`}
                        title={module.slug}
                      >
                        <span
                          className="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-white/10 text-[10px] uppercase text-slate-300"
                          aria-hidden
                        >
                          {(module.menu_icon || label).slice(0, 1)}
                        </span>
                        <span className="truncate">{label}</span>
                      </Link>
                    </li>
                  );
                })}
              </ul>
            </div>
          ))}
      </nav>
    </aside>
  );
}
