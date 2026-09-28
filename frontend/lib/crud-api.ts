const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api";

export type CrudRecord = Record<string, unknown> & {
  id?: number | string;
};

type LaravelResource<T> = {
  data: T;
  message?: string;
  meta?: Record<string, unknown>;
  links?: Record<string, unknown>;
};

type LaravelValidationError = {
  message?: string;
  errors?: Record<string, string[]>;
};

export type CrudFieldErrors = Record<string, string>;

export class CrudApiError extends Error {
  fieldErrors: CrudFieldErrors;

  constructor(message: string, fieldErrors: CrudFieldErrors = {}) {
    super(message);
    this.name = "CrudApiError";
    this.fieldErrors = fieldErrors;
  }
}

function mapValidationErrors(errors: Record<string, string[]>): CrudFieldErrors {
  const mapped: CrudFieldErrors = {};

  for (const [field, messages] of Object.entries(errors)) {
    const first = messages?.find((msg) => msg.trim() !== "");
    if (first) {
      mapped[field] = first;
    }
  }

  return mapped;
}

async function crudRequest<T>(
  path: string,
  init?: RequestInit
): Promise<T> {
  const response = await fetch(`${API_URL}${path}`, {
    ...init,
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(init?.headers ?? {}),
    },
    cache: "no-store",
  });

  const json = (await response.json().catch(() => ({}))) as
    | LaravelResource<T>
    | LaravelValidationError
    | T
    | { message?: string };

  if (!response.ok) {
    const validation = json as LaravelValidationError;
    if (validation.errors) {
      const fieldErrors = mapValidationErrors(validation.errors);
      const first =
        Object.values(fieldErrors)[0] || validation.message || "Validation failed";
      throw new CrudApiError(first, fieldErrors);
    }
    throw new CrudApiError(
      (json as { message?: string }).message || `Request failed (${response.status})`
    );
  }

  if (json && typeof json === "object" && "data" in json) {
    return (json as LaravelResource<T>).data;
  }

  return json as T;
}

function resourcePath(apiPrefix: string, id?: string | number) {
  const base = `/${apiPrefix.replace(/^\/+|\/+$/g, "")}`;
  return id === undefined ? base : `${base}/${id}`;
}

export type ListRecordsParams = {
  /** Per-field search terms; only sent for non-empty values */
  search?: Record<string, string>;
  sort_by?: string;
  sort_dir?: "asc" | "desc";
  per_page?: number;
};

export async function listRecords(
  apiPrefix: string,
  params?: ListRecordsParams
): Promise<CrudRecord[]> {
  const query = new URLSearchParams();

  if (params?.search) {
    Object.entries(params.search).forEach(([field, value]) => {
      const trimmed = value.trim();
      if (trimmed) {
        query.append(`search[${field}]`, trimmed);
      }
    });
  }

  if (params?.sort_by) {
    query.set("sort_by", params.sort_by);
    query.set("sort_dir", params.sort_dir ?? "asc");
  }

  if (params?.per_page) query.set("per_page", String(params.per_page));

  const qs = query.toString();
  const path = `${resourcePath(apiPrefix)}${qs ? `?${qs}` : ""}`;

  return crudRequest<CrudRecord[]>(path);
}

export async function getRecord(
  apiPrefix: string,
  id: string | number
): Promise<CrudRecord> {
  return crudRequest<CrudRecord>(resourcePath(apiPrefix, id));
}

export async function createRecord(
  apiPrefix: string,
  payload: Record<string, unknown>
): Promise<CrudRecord> {
  return crudRequest<CrudRecord>(resourcePath(apiPrefix), {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function updateRecord(
  apiPrefix: string,
  id: string | number,
  payload: Record<string, unknown>
): Promise<CrudRecord> {
  return crudRequest<CrudRecord>(resourcePath(apiPrefix, id), {
    method: "PUT",
    body: JSON.stringify(payload),
  });
}

export async function deleteRecord(
  apiPrefix: string,
  id: string | number
): Promise<void> {
  await crudRequest<unknown>(resourcePath(apiPrefix, id), {
    method: "DELETE",
  });
}
