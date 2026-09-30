import {
  CrudBuilderWizardState,
  FieldFormRow,
  fieldTypeUsesLength,
  FormListFormRow,
  ModuleInfoForm,
  parseEnumValuesList,
  parseValidationRulesJson,
  PermissionFormRow,
  RelationshipFormRow,
} from "@/lib/crud-builder";

export type WizardFieldErrors = Record<string, string>;

export function hasWizardFieldErrors(errors: WizardFieldErrors): boolean {
  return Object.keys(errors).length > 0;
}

export function validateStep1Module(module: ModuleInfoForm): WizardFieldErrors {
  const errors: WizardFieldErrors = {};

  if (!module.name.trim()) {
    errors.name = "Name is required.";
  }
  if (!module.slug.trim()) {
    errors.slug = "Slug is required.";
  }
  if (!module.table_name.trim()) {
    errors.table_name = "Table name is required.";
  } else if (!/^[a-z][a-z0-9_]*$/.test(module.table_name.trim())) {
    errors.table_name = "Table name must start with a letter and use snake_case.";
  }

  const version = module.api_version.trim();
  if (version && !/^v[0-9]+(\.[0-9]+)*$/.test(version)) {
    errors.api_version = "API version must look like v1 or v1.0.";
  }

  return errors;
}

export function validateStep2Fields(fields: FieldFormRow[]): WizardFieldErrors {
  const errors: WizardFieldErrors = {};

  if (!fields.length) {
    errors["fields._root"] = "Add at least one field.";
    return errors;
  }

  const nameToIds = new Map<string, string[]>();

  for (const field of fields) {
    const key = `fields.${field.id}.field_name`;

    if (!field.field_name.trim()) {
      errors[key] = "Field name is required.";
      continue;
    }

    if (!/^[a-z][a-z0-9_]*$/.test(field.field_name)) {
      errors[key] = "Field name must start with a letter and use snake_case.";
    }

    if (field.type === "enum" && !field.enum_values.trim()) {
      errors[`fields.${field.id}.enum_values`] =
        "Enter comma-separated enum values (e.g. active,inactive,draft).";
    }

    if (fieldTypeUsesLength(field.type) && field.length.trim()) {
      if (!/^\d+$/.test(field.length.trim())) {
        errors[`fields.${field.id}.length`] = "Length must be a number.";
      }
    }

    if (field.type === "enum" && field.default_value.trim()) {
      const options = parseEnumValuesList(field.enum_values);
      const defaultValue = field.default_value.trim();
      if (options.length > 0 && !options.includes(defaultValue)) {
        errors[`fields.${field.id}.default_value`] =
          "Default value must be one of the enum options.";
      }
    }

    const list = nameToIds.get(field.field_name) ?? [];
    list.push(field.id);
    nameToIds.set(field.field_name, list);
  }

  for (const ids of nameToIds.values()) {
    if (ids.length > 1) {
      for (const id of ids) {
        errors[`fields.${id}.field_name`] = "Field names must be unique.";
      }
    }
  }

  return errors;
}

export function validateStep3Relationships(
  relationships: RelationshipFormRow[]
): WizardFieldErrors {
  const errors: WizardFieldErrors = {};

  for (const rel of relationships) {
    if (!rel.related_module_id) {
      errors[`relations.${rel.id}.related_module_id`] = "Related module is required.";
    }
  }

  return errors;
}

export function validateStep4Forms(rows: FormListFormRow[]): WizardFieldErrors {
  const errors: WizardFieldErrors = {};

  if (!rows.length) {
    errors["forms._root"] = "No form fields to configure.";
    return errors;
  }

  for (const row of rows) {
    if (!row.form_label.trim()) {
      errors[`forms.${row.field_name}.form_label`] = "Label is required.";
    }

    try {
      parseValidationRulesJson(row.validation_rules_json);
    } catch {
      errors[`forms.${row.field_name}.validation_rules_json`] =
        "Validation rules must be valid JSON.";
    }
  }

  return errors;
}

export function validateStep5Listing(rows: FormListFormRow[]): WizardFieldErrors {
  const errors: WizardFieldErrors = {};

  for (const row of rows) {
    if (!row.list_label.trim()) {
      errors[`forms.${row.field_name}.list_label`] = "List label is required.";
    }

    const width = Number(row.width);
    if (!width || width < 1 || width > 100) {
      errors[`forms.${row.field_name}.width`] = "Width must be between 1 and 100.";
    }
  }

  return errors;
}

export function validateStep6Permissions(
  permissions: PermissionFormRow[]
): WizardFieldErrors {
  const errors: WizardFieldErrors = {};

  if (!permissions.length) {
    errors["permissions._root"] = "Add at least one permission.";
    return errors;
  }

  for (const perm of permissions) {
    if (!perm.permission_name.trim()) {
      errors[`permissions.${perm.id}.permission_name`] = "Permission name is required.";
    }
    if (!perm.action.trim()) {
      errors[`permissions.${perm.id}.action`] = "Action is required.";
    }
  }

  return errors;
}

export function validateWizardStep(
  step: number,
  wizard: CrudBuilderWizardState
): WizardFieldErrors {
  switch (step) {
    case 1:
      return validateStep1Module(wizard.module);
    case 2:
      return validateStep2Fields(wizard.fields);
    case 3:
      return validateStep3Relationships(wizard.relationships);
    case 4:
      return validateStep4Forms(wizard.formsList);
    case 5:
      return validateStep5Listing(wizard.formsList);
    case 6:
      return validateStep6Permissions(wizard.permissions);
    default:
      return {};
  }
}
