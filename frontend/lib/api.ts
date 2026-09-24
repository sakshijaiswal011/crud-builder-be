const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api";

export type CrudFieldMeta = {
  id: number;
  field_name: string;
  type: string;
  length: number | null;
  nullable: boolean;
  default_value: string | null;
  is_unique: boolean;
  is_indexed: boolean;
  comment: string | null;
};

export type CrudFormListMeta = {
  id: number;
  module_id: number;
  field_id: number;
  form_input_type: "text" | "number" | "select" | string;
  form_label: string;
  form_placeholder: string | null;
  is_required: boolean;
  validation_rules: string[] | Record<string, unknown> | null;
  list_label: string | null;
  search_enabled: boolean;
  sorting_enabled: boolean;
  filtering_enabled: boolean;
  width: number;
  field?: CrudFieldMeta | null;
};

export type CrudModule = {
  id: number;
  name: string;
  slug: string;
  table_name: string;
  api_prefix: string | null;
  menu_name: string | null;
  menu_icon: string | null;
  menu_group: string | null;
  status: string;
  soft_delete: boolean;
  fields_count?: number;
  relationships_count?: number;
  permissions_count?: number;
  fields?: CrudFieldMeta[];
  form_lists?: CrudFormListMeta[];
};

type ApiSuccess<T> = {
  success: true;
  code: number;
  message: string;
  data: T;
};

type ApiError = {
  success: false;
  code: number;
  message: string;
  errors?: unknown;
};

async function builderRequest<T>(path: string, init?: RequestInit): Promise<T> {
  const response = await fetch(`${API_URL}${path}`, {
    ...init,
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(init?.headers ?? {}),
    },
    cache: "no-store",
  });

  const json = (await response.json()) as ApiSuccess<T> | ApiError;

  if (!response.ok || !("success" in json) || !json.success) {
    const message =
      "message" in json && typeof json.message === "string"
        ? json.message
        : "API request failed";
    throw new Error(message);
  }

  return json.data;
}

export function getCrudModules() {
  return builderRequest<CrudModule[]>("/crud-modules");
}

export function getCrudModuleBySlug(slug: string) {
  return builderRequest<CrudModule>(`/crud-modules/slug/${encodeURIComponent(slug)}`);
}

export function getModuleHref(module: Pick<CrudModule, "slug">) {
  return `/admin/module/${module.slug}`;
}

export function getModuleLabel(module: Pick<CrudModule, "menu_name" | "name">) {
  return module.menu_name?.trim() || module.name;
}

export function getModuleApiPrefix(module: Pick<CrudModule, "api_prefix" | "slug">) {
  return (module.api_prefix || module.slug).replace(/^\/+|\/+$/g, "");
}

export function getFormFields(module: CrudModule): CrudFormListMeta[] {
  return (module.form_lists ?? []).filter((item) => item.field?.field_name);
}

export function getListColumns(module: CrudModule): CrudFormListMeta[] {
  return getFormFields(module).filter((item) => Boolean(item.list_label));
}

export function createCrudModule(payload: Record<string, unknown>) {
  return builderRequest<{ module: CrudModule; generated: Record<string, unknown> }>(
    "/crud-modules/create",
    {
      method: "POST",
      body: JSON.stringify(payload),
    }
  );
}
