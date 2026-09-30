"use client";

import { FormEvent, useEffect, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import Step1ModuleInfo from "@/app/components/admin/crud-builder/Step1ModuleInfo";
import Step2Fields from "@/app/components/admin/crud-builder/Step2Fields";
import Step3Relationships from "@/app/components/admin/crud-builder/Step3Relationships";
import Step4Forms from "@/app/components/admin/crud-builder/Step4Forms";
import Step5Listing from "@/app/components/admin/crud-builder/Step5Listing";
import Step6Permissions from "@/app/components/admin/crud-builder/Step6Permissions";
import Step7Generation from "@/app/components/admin/crud-builder/Step7Generation";
import WizardStepper from "@/app/components/admin/crud-builder/WizardStepper";
import { getCrudModuleBySlug, updateCrudModuleBySlug } from "@/lib/api";
import {
  buildUpdateModulePayload,
  CrudBuilderEditMeta,
  CrudBuilderWizardState,
  mapCrudModuleToWizardState,
  mergeFormsListWithFields,
} from "@/lib/crud-builder";
import {
  hasWizardFieldErrors,
  validateWizardStep,
  WizardFieldErrors,
} from "@/lib/crud-builder-validation";

export default function CrudBuilderEditPage() {
  const params = useParams<{ slug: string }>();
  const slug = decodeURIComponent(params.slug);
  const router = useRouter();

  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [currentStep, setCurrentStep] = useState(1);
  const [wizard, setWizard] = useState<CrudBuilderWizardState | null>(null);
  const [editMeta, setEditMeta] = useState<CrudBuilderEditMeta | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<WizardFieldErrors>({});

  useEffect(() => {
    let cancelled = false;

    async function load() {
      try {
        setLoading(true);
        setLoadError(null);
        const module = await getCrudModuleBySlug(slug);
        if (cancelled) return;
        const mapped = mapCrudModuleToWizardState(module);
        setWizard(mapped.state);
        setEditMeta(mapped.editMeta);
      } catch (err) {
        if (!cancelled) {
          setLoadError(err instanceof Error ? err.message : "Failed to load module");
        }
      } finally {
        if (!cancelled) setLoading(false);
      }
    }

    load();

    return () => {
      cancelled = true;
    };
  }, [slug]);

  async function submitUpdate(state: CrudBuilderWizardState) {
    try {
      setSubmitting(true);
      await updateCrudModuleBySlug(slug, buildUpdateModulePayload(state));
      alert("Module updated successfully.");
      router.push("/admin");
      router.refresh();
    } catch (err) {
      alert(err instanceof Error ? err.message : "Failed to update module.");
    } finally {
      setSubmitting(false);
    }
  }

  function goNext() {
    if (!wizard) return;

    const errors = validateWizardStep(currentStep, wizard);
    if (hasWizardFieldErrors(errors)) {
      setFieldErrors(errors);
      return;
    }

    setFieldErrors({});

    if (currentStep === 3) {
      setWizard({
        ...wizard,
        formsList: mergeFormsListWithFields(wizard.fields, wizard.formsList),
      });
    }

    if (currentStep === 7) {
      submitUpdate(wizard);
      return;
    }

    setCurrentStep((prev) => Math.min(prev + 1, 7));
  }

  function handleStepClick(step: number) {
    if (!wizard) return;
    setWizard(
      step >= 4
        ? { ...wizard, formsList: mergeFormsListWithFields(wizard.fields, wizard.formsList) }
        : wizard
    );
    setFieldErrors({});
    setCurrentStep(step);
  }

  function handleSubmit(event: FormEvent) {
    event.preventDefault();
    goNext();
  }

  if (loading) {
    return <p className="text-sm text-slate-500">Loading module builder…</p>;
  }

  if (loadError || !wizard || !editMeta) {
    return <p className="text-sm text-rose-600">{loadError ?? "Module not found."}</p>;
  }

  return (
    <div className="mx-auto max-w-6xl space-y-5">
      <div>
        <h2 className="text-xl font-semibold text-slate-900">Edit CRUD Module</h2>
        <p className="text-sm text-slate-500">{wizard.module.name} — slug and table name are locked.</p>
      </div>

      <div className="grid gap-5 lg:grid-cols-[240px_minmax(0,1fr)]">
        <aside className="h-fit rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:sticky lg:top-4">
          <WizardStepper currentStep={currentStep} maxReachableStep={7} onStepClick={handleStepClick} />
        </aside>

        <form noValidate onSubmit={handleSubmit} className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          {hasWizardFieldErrors(fieldErrors) ? (
            <div className="mb-5 rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
              Please fix the errors highlighted below before continuing.
            </div>
          ) : null}

          {currentStep === 1 ? (
            <Step1ModuleInfo
              value={wizard.module}
              errors={fieldErrors}
              lockIdentity
              onChange={(module) => {
                setFieldErrors({});
                setWizard((prev) => (prev ? { ...prev, module } : prev));
              }}
            />
          ) : null}
          {currentStep === 2 ? (
            <Step2Fields
              fields={wizard.fields}
              errors={fieldErrors}
              onChange={(fields) => {
                setFieldErrors({});
                setWizard((prev) => (prev ? { ...prev, fields } : prev));
              }}
            />
          ) : null}
          {currentStep === 3 ? (
            <Step3Relationships
              moduleName={wizard.module.name}
              currentSlug={wizard.module.slug}
              fields={wizard.fields}
              relationships={wizard.relationships}
              errors={fieldErrors}
              onChange={(relationships) => {
                setFieldErrors({});
                setWizard((prev) => (prev ? { ...prev, relationships } : prev));
              }}
            />
          ) : null}
          {currentStep === 4 ? (
            <Step4Forms
              rows={wizard.formsList}
              errors={fieldErrors}
              onChange={(formsList) => {
                setFieldErrors({});
                setWizard((prev) => (prev ? { ...prev, formsList } : prev));
              }}
            />
          ) : null}
          {currentStep === 5 ? (
            <Step5Listing
              rows={wizard.formsList}
              errors={fieldErrors}
              onChange={(formsList) => {
                setFieldErrors({});
                setWizard((prev) => (prev ? { ...prev, formsList } : prev));
              }}
            />
          ) : null}
          {currentStep === 6 ? (
            <Step6Permissions
              permissions={wizard.permissions}
              errors={fieldErrors}
              lockIdentity
              onChange={(permissions) => {
                setFieldErrors({});
                setWizard((prev) => (prev ? { ...prev, permissions } : prev));
              }}
            />
          ) : null}
          {currentStep === 7 ? (
            <Step7Generation
              value={wizard.generation}
              locked={editMeta.lockedGeneration}
              onChange={(generation) => setWizard((prev) => (prev ? { ...prev, generation } : prev))}
            />
          ) : null}

          <div className="mt-8 flex items-center justify-between border-t border-slate-100 pt-4">
            <button
              type="button"
              onClick={() => {
                setFieldErrors({});
                setCurrentStep((prev) => Math.max(prev - 1, 1));
              }}
              disabled={currentStep === 1 || submitting}
              className="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40"
            >
              Back
            </button>
            <button
              type="submit"
              disabled={submitting}
              className="rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-500 disabled:opacity-60"
            >
              {currentStep === 7 ? (submitting ? "Saving…" : "Save Module") : "Next"}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
