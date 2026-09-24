export const CRUD_BUILDER_STEPS = [
  { id: 1, title: "Module Info", description: "Basic module details" },
  { id: 2, title: "Fields", description: "Database columns" },
  { id: 3, title: "Relations", description: "Eloquent relationships" },
  { id: 4, title: "Forms", description: "Add / edit inputs" },
  { id: 5, title: "Listing", description: "Table columns" },
  { id: 6, title: "Permissions", description: "Access actions" },
  { id: 7, title: "Generate", description: "Output options" },
] as const;

export type ModuleStatus = "draft" | "active" | "inactive";

export type ModuleInfoForm = {
  name: string;
  slug: string;
  table_name: string;
  api_prefix: string;
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
    { id: crypto.randomUUID(), permission_name: `Export ${label}`, action: `${base}.export`, enabled: false },
  ];
}

export function buildFormsListFromFields(fields: FieldFormRow[]): FormListFormRow[] {
  return fields
    .filter((field) => field.field_name.trim())
    .map((field) => {
      const label = field.field_name
        .split("_")
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(" ");

      const inputType: FormInputType =
        field.type === "integer" || field.type === "decimal" ? "number" : "text";

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
  existing: FormListFormRow[]
): FormListFormRow[] {
  const byName = new Map(existing.map((row) => [row.field_name, row]));
  return buildFormsListFromFields(fields).map((row) => ({
    ...row,
    ...byName.get(row.field_name),
    field_name: row.field_name,
  }));
}

export function createInitialWizardState(): CrudBuilderWizardState {
  return {
    module: {
      name: "",
      slug: "",
      table_name: "",
      api_prefix: "",
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
