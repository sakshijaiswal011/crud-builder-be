"use client";

import { useParams, useRouter } from "next/navigation";
import CrudForm from "@/app/components/admin/CrudForm";
import { createRecord } from "@/lib/crud-api";
import { useModule } from "@/lib/useModule";

export default function ModuleCreatePage() {
  const params = useParams<{ slug: string }>();
  const router = useRouter();
  const slug = params.slug;
  const { module, loading, error, label, apiPrefix, formFields } = useModule(slug);

  if (loading) {
    return <p className="text-sm text-slate-500">Loading module…</p>;
  }

  if (error || !module) {
    return <p className="text-sm text-rose-600">{error ?? "Module not found."}</p>;
  }

  return (
    <div className="space-y-5">
      <div>
        <h2 className="text-xl font-semibold text-slate-900">Create {label}</h2>
        <p className="text-sm text-slate-500">Add a new record</p>
      </div>

      <CrudForm
        fields={formFields}
        submitLabel="Create"
        onCancel={() => router.push(`/admin/module/${slug}`)}
        onSubmit={async (values) => {
          await createRecord(apiPrefix, values);
          router.push(`/admin/module/${slug}`);
        }}
      />
    </div>
  );
}
