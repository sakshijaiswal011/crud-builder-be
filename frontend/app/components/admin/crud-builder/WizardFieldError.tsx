"use client";

import { WizardFieldErrors } from "@/lib/crud-builder-validation";

export const wizardInputClass =
  "mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500";

export const wizardCompactInputClass =
  "w-full rounded-md border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500";

export function inputClassWithError(baseClass: string, error?: string) {
  if (!error) return baseClass;

  return `${baseClass} border-rose-500 focus:border-rose-500 focus:ring-rose-500`;
}

export function FieldErrorText({ message }: { message?: string }) {
  if (!message) return null;

  return (
    <p className="mt-1 text-xs font-medium text-rose-600" role="alert">
      {message}
    </p>
  );
}

export function StepRootError({
  errors,
  keyName,
}: {
  errors?: WizardFieldErrors;
  keyName: string;
}) {
  const message = errors?.[keyName];
  if (!message) return null;

  return (
    <div
      className="rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"
      role="alert"
    >
      {message}
    </div>
  );
}
