"use client";

import { useEffect, useState } from "react";
import {
  CrudModule,
  getCrudModuleBySlug,
  getFormFields,
  getListColumns,
  getModuleApiPrefix,
  getModuleLabel,
} from "@/lib/api";

type UseModuleResult = {
  module: CrudModule | null;
  loading: boolean;
  error: string | null;
  label: string;
  apiPrefix: string;
  formFields: ReturnType<typeof getFormFields>;
  listColumns: ReturnType<typeof getListColumns>;
  reload: () => void;
};

export function useModule(slug: string | undefined): UseModuleResult {
  const [module, setModule] = useState<CrudModule | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [tick, setTick] = useState(0);

  useEffect(() => {
    if (!slug) return;

    let cancelled = false;

    async function load() {
      try {
        setLoading(true);
        setError(null);
        const data = await getCrudModuleBySlug(slug);
        if (!cancelled) {
          setModule(data);
        }
      } catch (err) {
        if (!cancelled) {
          setModule(null);
          setError(err instanceof Error ? err.message : "Failed to load module");
        }
      } finally {
        if (!cancelled) {
          setLoading(false);
        }
      }
    }

    load();

    return () => {
      cancelled = true;
    };
  }, [slug, tick]);

  return {
    module,
    loading,
    error,
    label: module ? getModuleLabel(module) : "",
    apiPrefix: module ? getModuleApiPrefix(module) : "",
    formFields: module ? getFormFields(module) : [],
    listColumns: module ? getListColumns(module) : [],
    reload: () => setTick((value) => value + 1),
  };
}
