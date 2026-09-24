"use client";

import { CRUD_BUILDER_STEPS } from "@/lib/crud-builder";

type WizardStepperProps = {
  currentStep: number;
  onStepClick?: (step: number) => void;
  maxReachableStep?: number;
};

export default function WizardStepper({
  currentStep,
  onStepClick,
  maxReachableStep = currentStep,
}: WizardStepperProps) {
  return (
    <nav aria-label="CRUD builder steps">
      <ol className="space-y-0">
        {CRUD_BUILDER_STEPS.map((step, index) => {
          const isActive = step.id === currentStep;
          const isCompleted = step.id < currentStep;
          const isReachable = step.id <= maxReachableStep;
          const isLast = index === CRUD_BUILDER_STEPS.length - 1;

          return (
            <li key={step.id} className="relative flex gap-3">
              <div className="flex flex-col items-center">
                <button
                  type="button"
                  disabled={!isReachable || !onStepClick}
                  onClick={() => onStepClick?.(step.id)}
                  className={`relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold transition ${
                    isActive || isCompleted
                      ? "bg-emerald-600 text-white"
                      : "border border-slate-300 bg-white text-slate-500"
                  } ${isReachable && onStepClick ? "cursor-pointer" : "cursor-default"}`}
                  aria-current={isActive ? "step" : undefined}
                >
                  {isCompleted && !isActive ? (
                    <svg viewBox="0 0 20 20" fill="currentColor" className="h-4 w-4" aria-hidden>
                      <path
                        fillRule="evenodd"
                        d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z"
                        clipRule="evenodd"
                      />
                    </svg>
                  ) : (
                    step.id
                  )}
                </button>

                {!isLast ? (
                  <span
                    className={`mt-1 w-0.5 flex-1 min-h-8 rounded ${
                      step.id < currentStep ? "bg-emerald-500" : "bg-slate-200"
                    }`}
                    aria-hidden
                  />
                ) : null}
              </div>

              <button
                type="button"
                disabled={!isReachable || !onStepClick}
                onClick={() => onStepClick?.(step.id)}
                className={`mb-6 flex-1 rounded-lg px-2 py-1 text-left transition ${
                  isActive ? "bg-emerald-50" : "bg-transparent"
                } ${isReachable && onStepClick ? "cursor-pointer hover:bg-emerald-50/70" : "cursor-default"}`}
              >
                <span
                  className={`block text-sm font-semibold ${
                    isActive ? "text-emerald-700" : isCompleted ? "text-emerald-800" : "text-slate-700"
                  }`}
                >
                  {step.title}
                </span>
                <span className="mt-0.5 block text-xs text-slate-400">{step.description}</span>
              </button>
            </li>
          );
        })}
      </ol>
    </nav>
  );
}
