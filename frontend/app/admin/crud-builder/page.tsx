"use client";

import { FormEvent, useState } from "react";
import { useRouter } from "next/navigation";
import Step1ModuleInfo from "@/app/components/admin/crud-builder/Step1ModuleInfo";
import Step2Fields from "@/app/components/admin/crud-builder/Step2Fields";
import Step3Relationships from "@/app/components/admin/crud-builder/Step3Relationships";
import Step4Forms from "@/app/components/admin/crud-builder/Step4Forms";
import Step5Listing from "@/app/components/admin/crud-builder/Step5Listing";
import Step6Permissions from "@/app/components/admin/crud-builder/Step6Permissions";
import Step7Generation from "@/app/components/admin/crud-builder/Step7Generation";
import WizardStepper from "@/app/components/admin/crud-builder/WizardStepper";
import { createCrudModule } from "@/lib/api";
import {
  buildCreateModulePayload,
  createDefaultPermissions,
  createInitialWizardState,
  CrudBuilderWizardState,
  mergeFormsListWithFields,
} from "@/lib/crud-builder";
import {
  hasWizardFieldErrors,
  validateWizardStep,
  WizardFieldErrors,
} from "@/lib/crud-builder-validation";

export default function CrudBuilderPage() {
  const router = useRouter();
  const [currentStep, setCurrentStep] = useState(1);
  const [maxReachableStep, setMaxReachableStep] = useState(1);
  const [wizard, setWizard] = useState<CrudBuilderWizardState>(createInitialWizardState);
  const [submitting, setSubmitting] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<WizardFieldErrors>({});

  function prepareStepData(step: number): CrudBuilderWizardState {
    let next = wizard;

    if (step >= 4) {
      next = {
        ...next,
        formsList: mergeFormsListWithFields(wizard.fields, wizard.formsList, wizard.relationships),
      };
    }

    if (step >= 6 && wizard.permissions.length === 0) {
      next = {
        ...next,
        permissions: createDefaultPermissions(wizard.module.name, wizard.module.slug),
      };
    }

    return next;
  }

  function advanceStep() {
    const next = Math.min(currentStep + 1, 7);
    setCurrentStep(next);
    setMaxReachableStep((prev) => Math.max(prev, next));
  }

  async function submitModule() {
    try {
      setSubmitting(true);
      const payload = buildCreateModulePayload(wizard);
      await createCrudModule(payload);
      
      const moduleSlug = wizard.module.slug;
      router.push(`/admin/module/${moduleSlug}`);
      router.refresh();
    } catch (err) {
      alert(err instanceof Error ? err.message : "Failed to create module.");
    } finally {
      setSubmitting(false);
    }
  }

  function goNext() {
    const errors = validateWizardStep(currentStep, wizard);

    if (hasWizardFieldErrors(errors)) {
      setFieldErrors(errors);
      return;
    }

    setFieldErrors({});
    let nextWizard = wizard;

    if (currentStep === 3) {
      nextWizard = {
        ...wizard,
        formsList: mergeFormsListWithFields(wizard.fields, wizard.formsList),
      };
      setWizard(nextWizard);
    }

    if (currentStep === 5) {
      nextWizard = {
        ...wizard,
        permissions:
          wizard.permissions.length > 0
            ? wizard.permissions
            : createDefaultPermissions(wizard.module.name, wizard.module.slug),
      };
      setWizard(nextWizard);
    }

    if (currentStep === 7) {
      submitModule();
      return;
    }

    advanceStep();
  }

  function goBack() {
    setFieldErrors({});
    setCurrentStep((prev) => Math.max(prev - 1, 1));
  }

  function handleStepClick(step: number) {
    if (step <= maxReachableStep) {
      const prepared = prepareStepData(step);
      setWizard(prepared);
      setFieldErrors({});
      setCurrentStep(step);
    }
  }

  function handleSubmit(event: FormEvent) {
    event.preventDefault();
    goNext();
  }

  return (
    <div className="mx-auto max-w-6xl space-y-5">
      <div>
        <h2 className="text-xl font-semibold text-slate-900">CRUD Builder</h2>
        <p className="text-sm text-slate-500">
          Create a new module step by step. Complete each step, then continue.
        </p>
      </div>

      <div className="grid gap-5 lg:grid-cols-[240px_minmax(0,1fr)]">
        <aside className="h-fit rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:sticky lg:top-4">
          <p className="mb-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
            Steps
          </p>
          <WizardStepper
            currentStep={currentStep}
            maxReachableStep={maxReachableStep}
            onStepClick={handleStepClick}
          />
        </aside>

        <form
          onSubmit={handleSubmit}
          className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
        >
          {hasWizardFieldErrors(fieldErrors) ? (
            <div
              className="mb-5 rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"
              role="alert"
            >
              Please fix the errors highlighted below before continuing.
            </div>
          ) : null}

          {currentStep === 1 ? (
            <Step1ModuleInfo
              value={wizard.module}
              errors={fieldErrors}
              onChange={(module) => {
                setFieldErrors({});
                setWizard((prev) => ({ ...prev, module }));
              }}
            />
          ) : null}

          {currentStep === 2 ? (
            <Step2Fields
              fields={wizard.fields}
              errors={fieldErrors}
              onChange={(fields) => {
                setFieldErrors({});
                setWizard((prev) => ({ ...prev, fields }));
              }}
            />
          ) : null}

          {currentStep === 3 ? (
            <Step3Relationships
              moduleName={wizard.module.name}
              currentSlug={wizard.module.slug}
              relationships={wizard.relationships}
              errors={fieldErrors}
              onChange={(relationships) => {
                setFieldErrors({});
                setWizard((prev) => ({ ...prev, relationships }));
              }}
            />
          ) : null}

          {currentStep === 4 ? (
            <Step4Forms
              rows={wizard.formsList}
              errors={fieldErrors}
              onChange={(formsList) => {
                setFieldErrors({});
                setWizard((prev) => ({ ...prev, formsList }));
              }}
            />
          ) : null}

          {currentStep === 5 ? (
            <Step5Listing
              rows={wizard.formsList}
              errors={fieldErrors}
              onChange={(formsList) => {
                setFieldErrors({});
                setWizard((prev) => ({ ...prev, formsList }));
              }}
            />
          ) : null}

          {currentStep === 6 ? (
            <Step6Permissions
              permissions={wizard.permissions}
              errors={fieldErrors}
              onChange={(permissions) => {
                setFieldErrors({});
                setWizard((prev) => ({ ...prev, permissions }));
              }}
            />
          ) : null}

          {currentStep === 7 ? (
            <Step7Generation
              value={wizard.generation}
              onChange={(generation) => setWizard((prev) => ({ ...prev, generation }))}
            />
          ) : null}

          <div className="mt-8 flex items-center justify-between border-t border-slate-100 pt-4">
            <button
              type="button"
              onClick={goBack}
              disabled={currentStep === 1 || submitting}
              className="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
            >
              Back
            </button>

            <div className="flex items-center gap-3">
              <span className="text-xs text-slate-400">Step {currentStep} of 7</span>
              <button
                type="submit"
                disabled={submitting}
                className="rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-500 disabled:opacity-60"
              >
                {currentStep === 7
                  ? submitting
                    ? "Creating…"
                    : "Create Module"
                  : "Next"}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
}
