"use client";

import { useEffect, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import CrudForm from "@/app/components/admin/CrudForm";
import { CrudRecord, getRecord, updateRecord } from "@/lib/crud-api";
import { useModule } from "@/lib/useModule";

export default function ModuleEditPage() {
  const params = useParams<{ slug: string; id: string }>();
  const router = useRouter();
  const slug = params.slug;
  const id = params.id;

  const { module, loading, error, label, apiPrefix, formFields } = useModule(slug);
  const [record, setRecord] = useState<CrudRecord | null>(null);
  const [recordLoading, setRecordLoading] = useState(true);
  const [recordError, setRecordError] = useState<string | null>(null);

  useEffect(() => {
    if (!apiPrefix || !id) return;

    let cancelled = false;

    async function load() {
      try {
        setRecordLoading(true);
        setRecordError(null);
        const data = await getRecord(apiPrefix, id);
        if (!cancelled) {
          setRecord(data);
        }
      } catch (err) {
        if (!cancelled) {
          setRecord(null);
          setRecordError(err instanceof Error ? err.message : "Failed to load record");
        }
      } finally {
        if (!cancelled) {
          setRecordLoading(false);
        }
      }
    }

    load();

    return () => {
      cancelled = true;
    };
  }, [apiPrefix, id]);

  if (loading || recordLoading) {
    return <p className="text-sm text-slate-500">Loading…</p>;
  }

  if (error || !module) {
    return <p className="text-sm text-rose-600">{error ?? "Module not found."}</p>;
  }

  if (recordError || !record) {
    return <p className="text-sm text-rose-600">{recordError ?? "Record not found."}</p>;
  }

  return (
    <div className="space-y-5">
      <div>
        <h2 className="text-xl font-semibold text-slate-900">Edit {label}</h2>
        <p className="text-sm text-slate-500">Update record #{id}</p>
      </div>

      <CrudForm
        fields={formFields}
        initialValues={record}
        submitLabel="Update"
        onCancel={() => router.push(`/admin/module/${slug}`)}
        onSubmit={async (values) => {
          await updateRecord(apiPrefix, id, values);
          router.push(`/admin/module/${slug}`);
        }}
      />
    </div>
  );
}
