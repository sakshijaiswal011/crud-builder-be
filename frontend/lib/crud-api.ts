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
      const first = Object.values(validation.errors)[0]?.[0];
      throw new Error(first || validation.message || "Validation failed");
    }
    throw new Error(
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

export async function listRecords(
  apiPrefix: string,
  params?: { search?: string; per_page?: number }
): Promise<CrudRecord[]> {
  const query = new URLSearchParams();
  if (params?.search) query.set("search", params.search);
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
