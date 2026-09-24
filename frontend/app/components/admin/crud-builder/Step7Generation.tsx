"use client";

import { GenerationForm } from "@/lib/crud-builder";

type Step7GenerationProps = {
  value: GenerationForm;
  onChange: (value: GenerationForm) => void;
};

export default function Step7Generation({ value, onChange }: Step7GenerationProps) {
  function toggle(key: keyof GenerationForm) {
    onChange({ ...value, [key]: !value[key] });
  }

  const options: { key: keyof GenerationForm; label: string; description: string }[] = [
    {
      key: "generate_api_controller_routes",
      label: "Generate API controller & routes (API endpoints)",
      description: "Create REST controller and register module API routes.",
    },
    {
      key: "generate_api_resource",
      label: "Generate API resource",
      description: "Create JSON resource transformer for API responses.",
    },
    {
      key: "generate_policy",
      label: "Generate policy",
      description: "Create authorization policy for this module.",
    },
    {
      key: "generate_frontend_views",
      label: "Generate frontend views (list and form pages inside this builder)",
      description: "Prepare frontend list and form pages for the admin module.",
    },
  ];

  return (
    <div className="space-y-5">
      <div>
        <h3 className="text-lg font-semibold text-slate-900">
          API configure and API endpoints / frontend code generation
        </h3>
        <p className="text-sm text-slate-500">
          Choose what should be generated when you create this module.
        </p>
      </div>

      <div className="space-y-3">
        {options.map((option) => (
          <label
            key={option.key}
            className="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 bg-slate-50/60 p-4 hover:bg-slate-50"
          >
            <input
              type="checkbox"
              checked={value[option.key]}
              onChange={() => toggle(option.key)}
              className="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
            />
            <span>
              <span className="block text-sm font-medium text-slate-800">{option.label}</span>
              <span className="mt-0.5 block text-xs text-slate-500">{option.description}</span>
            </span>
          </label>
        ))}
      </div>
    </div>
  );
}
