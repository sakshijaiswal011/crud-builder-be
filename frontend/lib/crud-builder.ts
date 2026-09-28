export const CRUD_BUILDER_STEPS = [
  { id: 1, title: "Module Info", description: "Basic module details" },
  { id: 2, title: "Fields", description: "Database columns" },
  { id: 3, title: "Relations", description: "Eloquent relationships" },
  { id: 4, title: "Forms", description: "Add / edit inputs" },
  { id: 5, title: "Listing", description: "Table columns" },
  { id: 6, title: "Permissions", description: "Access actions" },
  { id: 7, title: "Generate", description: "Output options" },
] as const;

/** Set to true to show the Filterable checkbox on step 5 (listing). */
export const SHOW_STEP5_FILTERABLE = false;

export type ModuleStatus = "draft" | "active" | "inactive";

export type ModuleInfoForm = {
  name: string;
  slug: string;
  table_name: string;
  api_prefix: string;
  api_version: string;
  menu_name: string;
  menu_icon: string;
  menu_icon_file_name: string;
  menu_group: string;
  soft_delete: boolean;
  audit_log: boolean;
  status: ModuleStatus;
};

export type RelationType = "hasOne" | "hasMany" | "belongsTo" | "belongsToMany";

export type FieldType =
  | "string"
  | "text"
  | "integer"
  | "bigInteger"
  | "decimal"
  | "boolean"
  | "date"
  | "datetime"
  | "timestamp"
  | "json"
  | "uuid"
  | "foreignId";

export type FieldFormRow = {
  id: string;
  db_id?: number;
  field_name: string;
  type: FieldType;
  length: string;
  nullable: boolean;
  default_value: string;
  is_unique: boolean;
  is_indexed: boolean;
  comment: string;
};

export type RelationshipFormRow = {
  id: string;
  relation_type: RelationType;
  related_module_id: string;
  foreign_key: string;
  local_key: string;
  display_field: string;
  display_name: string;
};

export type FormInputType = "text" | "number" | "select";

export type FormListFormRow = {
  field_name: string;
  form_input_type: FormInputType;
  form_label: string;
  form_placeholder: string;
  is_required: boolean;
  validation_rules_json: string;
  list_label: string;
  search_enabled: boolean;
  sorting_enabled: boolean;
  filtering_enabled: boolean;
  width: string;
};

export type PermissionFormRow = {
  id: string;
  db_id?: number;
  permission_name: string;
  action: string;
  enabled: boolean;
};

export type GenerationForm = {
  generate_api_controller_routes: boolean;
  generate_api_resource: boolean;
  generate_policy: boolean;
  generate_frontend_views: boolean;
};

export type CrudBuilderWizardState = {
  module: ModuleInfoForm;
  fields: FieldFormRow[];
  relationships: RelationshipFormRow[];
  formsList: FormListFormRow[];
  permissions: PermissionFormRow[];
  generation: GenerationForm;
};

export type CrudBuilderEditMeta = {
  lockedGeneration: GenerationForm;
};

export const FIELD_TYPE_OPTIONS: { value: FieldType; label: string }[] = [
  { value: "string", label: "string" },
  { value: "text", label: "text" },
  { value: "integer", label: "integer" },
  { value: "bigInteger", label: "bigInteger" },
  { value: "decimal", label: "decimal" },
  { value: "boolean", label: "boolean" },
  { value: "date", label: "date" },
  { value: "datetime", label: "datetime" },
  { value: "timestamp", label: "timestamp" },
  { value: "json", label: "json" },
  { value: "uuid", label: "uuid" },
  { value: "foreignId", label: "foreignId" },
];

export const RELATION_TYPE_OPTIONS: { value: RelationType; label: string }[] = [
  { value: "hasOne", label: "hasOne" },
  { value: "hasMany", label: "hasMany" },
  { value: "belongsTo", label: "belongsTo" },
  { value: "belongsToMany", label: "belongsToMany" },
];

export const FORM_INPUT_TYPE_OPTIONS: { value: FormInputType; label: string }[] = [
  { value: "text", label: "text" },
  { value: "number", label: "number" },
  { value: "select", label: "select" },
];

export function createEmptyField(): FieldFormRow {
  return {
    id: crypto.randomUUID(),
    field_name: "",
    type: "string",
    length: "255",
    nullable: false,
    default_value: "",
    is_unique: false,
    is_indexed: false,
    comment: "",
  };
}

export function createEmptyRelationship(): RelationshipFormRow {
  return {
    id: crypto.randomUUID(),
    relation_type: "belongsTo",
    related_module_id: "",
    foreign_key: "",
    local_key: "id",
    display_field: "",
    display_name: "",
  };
}

export function createDefaultPermissions(moduleName: string, slug: string): PermissionFormRow[] {
  const base = slug || "module";
  const label = moduleName.trim() || "Module";

  return [
    { id: crypto.randomUUID(), permission_name: `View ${label}`, action: `${base}.view`, enabled: true },
    { id: crypto.randomUUID(), permission_name: `Create ${label}`, action: `${base}.create`, enabled: true },
    { id: crypto.randomUUID(), permission_name: `Update ${label}`, action: `${base}.update`, enabled: true },
    { id: crypto.randomUUID(), permission_name: `Delete ${label}`, action: `${base}.delete`, enabled: true },
    { id: crypto.randomUUID(), permission_name: `Export ${label}`, action: `${base}.export`, enabled: true },
  ];
}

export function buildFormsListFromFields(
  fields: FieldFormRow[],
  relationships: RelationshipFormRow[] = []
): FormListFormRow[] {
  return fields
    .filter((field) => field.field_name.trim())
    .map((field) => {
      const relationship = relationships.find(r => r.foreign_key === field.field_name);
      
      let label = field.field_name
        .split("_")
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(" ");

      if (relationship && relationship.display_name) {
        label = relationship.display_name;
      }

      const inputType: FormInputType = relationship
        ? "select"
        : field.type === "integer" || field.type === "decimal"
        ? "number"
        : "text";

      return {
        field_name: field.field_name,
        form_input_type: inputType,
        form_label: label,
        form_placeholder: `Enter ${label.toLowerCase()}`,
        is_required: !field.nullable,
        validation_rules_json: field.nullable ? '["nullable"]' : '["required"]',
        list_label: label,
        search_enabled: true,
        sorting_enabled: true,
        filtering_enabled: true,
        width: "25",
      };
    });
}

export function mergeFormsListWithFields(
  fields: FieldFormRow[],
  existing: FormListFormRow[],
  relationships: RelationshipFormRow[] = []
): FormListFormRow[] {
  const byName = new Map(existing.map((row) => [row.field_name, row]));
  return buildFormsListFromFields(fields, relationships).map((row) => {
    const existingRow = byName.get(row.field_name);
    const displayName = relationships.find(r => r.foreign_key === row.field_name)?.display_name;
    const finalLabel = displayName || existingRow?.form_label || row.form_label;
    
    return {
      ...row,
      ...existingRow,
      form_label: finalLabel,
      list_label: displayName || existingRow?.list_label || row.list_label,
      form_placeholder: displayName ? `Enter ${displayName.toLowerCase()}` : (existingRow?.form_placeholder || row.form_placeholder),
      field_name: row.field_name,
    };
  });
}

export function createInitialWizardState(): CrudBuilderWizardState {
  return {
    module: {
      name: "",
      slug: "",
      table_name: "",
      api_prefix: "",
      api_version: "v1",
      menu_name: "",
      menu_icon: "",
      menu_icon_file_name: "",
      menu_group: "",
      soft_delete: false,
      audit_log: false,
      status: "draft",
    },
    fields: [createEmptyField()],
    relationships: [],
    formsList: [],
    permissions: [],
    generation: {
      generate_api_controller_routes: true,
      generate_api_resource: true,
      generate_policy: true,
      generate_frontend_views: true,
    },
  };
}

export function slugify(value: string): string {
  return value
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}

export function toTableName(value: string): string {
  return value
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "_")
    .replace(/^_+|_+$/g, "");
}

export function parseValidationRulesJson(raw: string): unknown {
  const trimmed = raw.trim();
  if (!trimmed) return [];
  return JSON.parse(trimmed);
}

export function buildCreateModulePayload(state: CrudBuilderWizardState): Record<string, unknown> {
  const m = state.module;

  return {
    name: m.name.trim(),
    slug: m.slug.trim(),
    table_name: m.table_name.trim(),
    api_prefix: m.api_prefix.trim() || null,
    api_version: m.api_version.trim() || "v1",
    menu_name: m.menu_name.trim() || null,
    menu_icon: m.menu_icon_file_name.trim() || null,
    menu_group: m.menu_group.trim() || null,
    soft_delete: m.soft_delete,
    audit_log: m.audit_log,
    status: m.status,
    generate_api_controller_routes: state.generation.generate_api_controller_routes,
    generate_api_resource: state.generation.generate_api_resource,
    generate_policy: state.generation.generate_policy,
    generate_frontend_views: state.generation.generate_frontend_views,
    fields: state.fields.map((field) => ({
      field_name: field.field_name,
      type: field.type,
      length: field.length ? Number(field.length) : null,
      nullable: field.nullable,
      default_value: field.default_value || null,
      is_unique: field.is_unique,
      is_indexed: field.is_indexed,
      comment: field.comment || null,
    })),
    relationships: state.relationships
      .filter((rel) => rel.related_module_id)
      .map((rel) => ({
        relation_type: rel.relation_type,
        related_module_id: Number(rel.related_module_id),
        foreign_key: rel.foreign_key || null,
        local_key: rel.local_key || "id",
        display_field: rel.display_field || null,
        display_name: rel.display_name || null,
      })),
    forms_list: state.formsList.map((row) => ({
      field_name: row.field_name,
      form_label: row.form_label,
      form_input_type: row.form_input_type,
      form_placeholder: row.form_placeholder || "",
      is_required: row.is_required,
      validation_rules: parseValidationRulesJson(row.validation_rules_json),
      list_label: row.list_label,
      search_enabled: row.search_enabled,
      sorting_enabled: row.sorting_enabled,
      filtering_enabled: row.filtering_enabled,
      width: Number(row.width) || 25,
    })),
    permissions: state.permissions.map((perm) => ({
      permission_name: perm.permission_name,
      action: perm.action,
      enabled: perm.enabled,
    })),
  };
}

function validationRulesToJson(rules: unknown): string {
  if (!rules) return "[]";
  if (typeof rules === "string") return rules;
  try {
    return JSON.stringify(rules);
  } catch {
    return "[]";
  }
}

export function mapCrudModuleToWizardState(
  module: import("@/lib/api").CrudModule
): { state: CrudBuilderWizardState; editMeta: CrudBuilderEditMeta } {
  const formByFieldId = new Map(
    (module.form_lists ?? []).map((row) => [row.field_id, row])
  );

  const fields: FieldFormRow[] = (module.fields ?? []).map((field) => ({
    id: String(field.id),
    db_id: field.id,
    field_name: field.field_name,
    type: (field.type as FieldType) || "string",
    length: field.length ? String(field.length) : "",
    nullable: field.nullable,
    default_value: field.default_value ?? "",
    is_unique: field.is_unique,
    is_indexed: field.is_indexed,
    comment: field.comment ?? "",
  }));

  const formsList: FormListFormRow[] = fields.map((field) => {
    const meta = formByFieldId.get(field.db_id!);
    return {
      field_name: field.field_name,
      form_input_type: (meta?.form_input_type as FormInputType) || "text",
      form_label: meta?.form_label ?? field.field_name,
      form_placeholder: meta?.form_placeholder ?? "",
      is_required: meta?.is_required ?? false,
      validation_rules_json: validationRulesToJson(meta?.validation_rules),
      list_label: meta?.list_label ?? field.field_name,
      search_enabled: meta?.search_enabled ?? true,
      sorting_enabled: meta?.sorting_enabled ?? true,
      filtering_enabled: meta?.filtering_enabled ?? false,
      width: String(meta?.width ?? 25),
    };
  });

  const generation: GenerationForm = {
    generate_api_controller_routes: Boolean(module.generate_api_controller_routes),
    generate_api_resource: Boolean(module.generate_api_resource),
    generate_policy: Boolean(module.generate_policy),
    generate_frontend_views: Boolean(module.generate_frontend_views),
  };

  return {
    state: {
      module: {
        name: module.name,
        slug: module.slug,
        table_name: module.table_name,
        api_prefix: module.api_prefix ?? module.slug,
        api_version: module.api_version ?? "v1",
        menu_name: module.menu_name ?? "",
        menu_icon: "",
        menu_icon_file_name: module.menu_icon ?? "",
        menu_group: module.menu_group ?? "",
        soft_delete: Boolean(module.soft_delete),
        audit_log: Boolean(module.audit_log),
        status: (module.status as ModuleStatus) || "draft",
      },
      fields,
      relationships: (module.relationships ?? []).map((rel) => ({
        id: String(rel.id),
        relation_type: rel.relation_type as RelationType,
        related_module_id: String(rel.related_module_id),
        foreign_key: rel.foreign_key ?? "",
        local_key: rel.local_key ?? "id",
        display_field: rel.display_field ?? "",
        display_name: rel.display_name ?? "",
      })),
      formsList,
      permissions: (module.permissions ?? []).map((perm) => ({
        id: String(perm.id),
        db_id: perm.id,
        permission_name: perm.permission_name,
        action: perm.action,
        enabled: perm.enabled,
      })),
      generation,
    },
    editMeta: { lockedGeneration: { ...generation } },
  };
}

export function buildUpdateModulePayload(state: CrudBuilderWizardState): Record<string, unknown> {
  const base = buildCreateModulePayload(state);

  return {
    ...base,
    fields: state.fields.map((field) => ({
      ...(field.db_id ? { id: field.db_id } : {}),
      field_name: field.field_name,
      type: field.type,
      length: field.length ? Number(field.length) : null,
      nullable: field.nullable,
      default_value: field.default_value || null,
      is_unique: field.is_unique,
      is_indexed: field.is_indexed,
      comment: field.comment || null,
    })),
    permissions: state.permissions.map((perm) => ({
      id: perm.db_id,
      permission_name: perm.permission_name,
      action: perm.action,
      enabled: perm.enabled,
    })),
  };
}
