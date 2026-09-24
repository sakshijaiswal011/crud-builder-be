# CRUD Builder --- Complete Database Structure

## 1. Overview

This document defines the recommended database architecture for a
Laravel + Next.js CRUD Builder.

The CRUD Builder uses two conceptual layers:

1.  **Builder Metadata Database** --- stores module, field,
    relationship, form, list, permission, API, version, and generation
    configuration.
2.  **Generated Application** --- the Laravel code and application
    database generated from that metadata.

``` text
CRUD BUILDER
    |
    +----------------------+----------------------+
    |                                             |
    v                                             v
Builder Metadata DB                    Generated Application
    |                                             |
    +-- modules                                  +-- migrations
    +-- fields                                   +-- Models
    +-- relationships                            +-- Controllers
    +-- forms                                    +-- Requests
    +-- lists                                    +-- Resources
    +-- permissions                              +-- Policies
    +-- API configuration                        +-- Routes
    +-- generation history                       +-- Next.js pages
```

The builder should treat its metadata as the source of truth. Generated
files are outputs of that configuration.

------------------------------------------------------------------------
## your builder stores this definition in its own tables.
``` text 
    POST /api/v1/modules/{module}/generate

    can produce:

    app/Models/ProductCategory.php
    app/Http/Controllers/Api/ProductCategoryController.php
    app/Http/Requests/ProductCategory/StoreProductCategoryRequest.php
    app/Http/Requests/ProductCategory/UpdateProductCategoryRequest.php
    app/Http/Resources/ProductCategoryResource.php
    app/Policies/ProductCategoryPolicy.php
    database/migrations/xxxx_create_product_categories_table.php
    routes/modules/product_category.php

    And later the Next.js generator can produce:

    app/(dashboard)/product-categories/page.tsx
    app/(dashboard)/product-categories/create/page.tsx
    app/(dashboard)/product-categories/[id]/edit/page.tsx
```
------------------------------------------------------------------------

# 2. Your 8 steps CRUD Builder Flow

  Step   Name                 Main Purpose
  ------ -------------------- ----------------------------------------
  1      Module Information   Define module/table/API identity
  2      Database Fields      Define database columns
  3      Relationships        Define Eloquent/database relationships
  4      Form Configuration   Define create/edit form metadata
  5      List Configuration   Define listing/table metadata
  6      Permissions          Define CRUD authorization
  7      API & Frontend       Define API and frontend generation
  8      Review & Generate    Validate, preview, and generate

------------------------------------------------------------------------

# 3. Complete Table List

## Authentication / Authorization

Laravel and Spatie provide:

``` text
users
roles
permissions
model_has_roles
model_has_permissions
role_has_permissions
```

## CRUD Builder

``` text
crud_modules
crud_fields
crud_relationships

crud_forms
crud_form_fields

crud_lists
crud_list_columns
crud_list_filters
crud_list_actions

crud_module_permissions

crud_api_configs

crud_module_versions
crud_generation_runs
crud_generation_files
```

------------------------------------------------------------------------

# 4. Entity Relationship Overview

``` text
users
  |
  +-- created_by / updated_by

crud_modules
  |
  +-- 1:N crud_fields
  |
  +-- 1:N crud_relationships
  |
  +-- 1:N crud_forms
  |       |
  |       +-- 1:N crud_form_fields
  |
  +-- 1:N crud_lists
  |       |
  |       +-- 1:N crud_list_columns
  |       +-- 1:N crud_list_filters
  |
  +-- 1:N crud_module_permissions
  |
  +-- 1:1 crud_api_configs
  |
  +-- 1:N crud_module_versions
  |
  +-- 1:N crud_generation_runs
          |
          +-- 1:N crud_generation_files
```

------------------------------------------------------------------------

# 5. `users`

Use Laravel's standard users table.

  Column              Type              Attributes
  ------------------- ----------------- --------------------
  id                  BIGINT UNSIGNED   PK, AUTO_INCREMENT
  name                VARCHAR(255)      NOT NULL
  email               VARCHAR(255)      NOT NULL, UNIQUE
  email_verified_at   TIMESTAMP         NULL
  password            VARCHAR(255)      NOT NULL
  remember_token      VARCHAR(100)      NULL
  created_at          TIMESTAMP         NULL
  updated_at          TIMESTAMP         NULL

Optional later:

``` text
status
last_login_at
last_login_ip
```

------------------------------------------------------------------------

# 6. Spatie Permission Tables

Use Spatie's standard permission tables.

## `roles`

``` text
id                  BIGINT UNSIGNED PK
name                VARCHAR(255)
guard_name          VARCHAR(255)
created_at          TIMESTAMP NULL
updated_at          TIMESTAMP NULL

UNIQUE(name, guard_name)
```

## `permissions`

``` text
id                  BIGINT UNSIGNED PK
name                VARCHAR(255)
guard_name          VARCHAR(255)
created_at          TIMESTAMP NULL
updated_at          TIMESTAMP NULL

UNIQUE(name, guard_name)
```

## `model_has_roles`

``` text
role_id             BIGINT UNSIGNED
model_type          VARCHAR(255)
model_id            BIGINT UNSIGNED

PRIMARY KEY(role_id, model_id, model_type)

INDEX(model_id, model_type)
```

## `model_has_permissions`

``` text
permission_id       BIGINT UNSIGNED
model_type          VARCHAR(255)
model_id            BIGINT UNSIGNED

PRIMARY KEY(permission_id, model_id, model_type)

INDEX(model_id, model_type)
```

## `role_has_permissions`

``` text
permission_id       BIGINT UNSIGNED
role_id             BIGINT UNSIGNED

PRIMARY KEY(permission_id, role_id)
```

------------------------------------------------------------------------

# 7. `crud_modules`

This is the root table. Every CRUD module has one record here.

Example:

``` text
Name:       Product Category
Table:      product_categories
API Prefix: product-categories
```

## Columns

  Column         Type              Attributes
  -------------- ----------------- -------------------------
  id             BIGINT UNSIGNED   PK, AUTO_INCREMENT
  uuid           CHAR(36)          UNIQUE
  name           VARCHAR(100)      NOT NULL
  slug           VARCHAR(100)      NOT NULL, UNIQUE
  display_name   VARCHAR(150)      NOT NULL
  table_name     VARCHAR(150)      NOT NULL, UNIQUE
  model_name     VARCHAR(150)      NOT NULL
  api_prefix     VARCHAR(150)      NULL
  route_prefix   VARCHAR(150)      NULL
  menu_label     VARCHAR(150)      NULL
  menu_group     VARCHAR(100)      NULL
  menu_icon      VARCHAR(100)      NULL
  description    TEXT              NULL
  soft_delete    BOOLEAN           DEFAULT FALSE
  audit_log      BOOLEAN           DEFAULT FALSE
  status         ENUM              draft, active, disabled
  sort_order     INT               DEFAULT 0
  created_by     BIGINT UNSIGNED   NULL
  updated_by     BIGINT UNSIGNED   NULL
  created_at     TIMESTAMP         NULL
  updated_at     TIMESTAMP         NULL
  deleted_at     TIMESTAMP         NULL

## Indexes

``` text
UNIQUE(uuid)
UNIQUE(slug)
UNIQUE(table_name)

INDEX(status)
INDEX(menu_group)
INDEX(created_by)
INDEX(updated_by)
```

------------------------------------------------------------------------

# 8. Module Naming

Do not allow the frontend to provide arbitrary PHP class names.

Use a backend naming service:

``` text
ModuleNamingService
```

For example:

``` text
Input:
Product Category
```

Output:

``` json
{
    "singular": "product_category",
    "plural": "product_categories",
    "model": "ProductCategory",
    "table": "product_categories",
    "route": "product-categories",
    "api": "product-categories",
    "label": "Product Category"
}
```

This prevents generator naming inconsistencies.

------------------------------------------------------------------------

# 9. `crud_fields`

This stores database field definitions.

## Columns

  Column              Type              Attributes
  ------------------- ----------------- --------------------
  id                  BIGINT UNSIGNED   PK, AUTO_INCREMENT
  module_id           BIGINT UNSIGNED   FK
  name                VARCHAR(100)      NOT NULL
  label               VARCHAR(150)      NULL
  type                VARCHAR(50)       NOT NULL
  length              INT               NULL
  precision_value     INT               NULL
  scale_value         INT               NULL
  nullable            BOOLEAN           DEFAULT FALSE
  default_value       TEXT              NULL
  is_primary          BOOLEAN           DEFAULT FALSE
  is_auto_increment   BOOLEAN           DEFAULT FALSE
  is_unsigned         BOOLEAN           DEFAULT FALSE
  is_unique           BOOLEAN           DEFAULT FALSE
  is_indexed          BOOLEAN           DEFAULT FALSE
  index_name          VARCHAR(150)      NULL
  enum_values         JSON              NULL
  comment             TEXT              NULL
  validation_rules    JSON              NULL
  options             JSON              NULL
  sort_order          INT               DEFAULT 0
  created_at          TIMESTAMP         NULL
  updated_at          TIMESTAMP         NULL

## Foreign Key

``` text
module_id
    -> crud_modules.id
    ON DELETE CASCADE
```

## Constraints

``` text
UNIQUE(module_id, name)
INDEX(module_id, sort_order)
```

------------------------------------------------------------------------

# 10. Supported Database Field Types

Start with a manageable set:

``` text
string
text
longText

integer
bigInteger
unsignedBigInteger
smallInteger
tinyInteger

decimal
float
double

boolean

date
datetime
timestamp
time

json

uuid
ulid

foreignId

enum
```

Do not implement dozens of database types in V1.

------------------------------------------------------------------------

# 11. UI Field Types

Database type and UI field type should be treated separately.

Example:

``` text
database_type = bigint
field_type    = relationship
```

Recommended UI types:

``` text
text
textarea
number
email
password
select
multiselect
checkbox
radio
switch
date
datetime
file
image
richtext
relationship-select
hidden
```

------------------------------------------------------------------------

# 12. `crud_relationships`

Stores Laravel Eloquent relationship metadata.

## Columns

  Column              Type              Attributes
  ------------------- ----------------- --------------------
  id                  BIGINT UNSIGNED   PK, AUTO_INCREMENT
  module_id           BIGINT UNSIGNED   FK
  name                VARCHAR(100)      NOT NULL
  type                ENUM              Relationship type
  related_module_id   BIGINT UNSIGNED   NULL, FK
  foreign_key         VARCHAR(100)      NULL
  local_key           VARCHAR(100)      NULL
  related_key         VARCHAR(100)      NULL
  pivot_table         VARCHAR(150)      NULL
  pivot_foreign_key   VARCHAR(100)      NULL
  pivot_related_key   VARCHAR(100)      NULL
  on_delete           VARCHAR(30)       NULL
  on_update           VARCHAR(30)       NULL
  options             JSON              NULL
  sort_order          INT               DEFAULT 0
  created_at          TIMESTAMP         NULL
  updated_at          TIMESTAMP         NULL

## Relationship Types

``` text
belongsTo
hasOne
hasMany
belongsToMany
morphTo
morphMany
```

For V1, prioritize:

``` text
belongsTo
hasOne
hasMany
belongsToMany
```

## Foreign Keys

``` text
module_id
    -> crud_modules.id
    ON DELETE CASCADE

related_module_id
    -> crud_modules.id
    ON DELETE SET NULL
```

Example:

``` text
Product
    belongsTo
ProductCategory

foreign_key:
category_id

owner_key:
id
```

Generated Laravel relationship:

``` php
public function category()
{
    return $this->belongsTo(ProductCategory::class, 'category_id');
}
```

------------------------------------------------------------------------

# 13. `crud_forms`

A module can have multiple forms, such as Create and Edit.

## Columns

  Column       Type              Attributes
  ------------ ----------------- ----------------------------------
  id           BIGINT UNSIGNED   PK, AUTO_INCREMENT
  module_id    BIGINT UNSIGNED   FK
  name         VARCHAR(100)      NOT NULL
  type         ENUM              create, edit
  layout       ENUM              single, two_column, three_column
  config       JSON              NULL
  created_at   TIMESTAMP         NULL
  updated_at   TIMESTAMP         NULL

## Constraints

``` text
UNIQUE(module_id, type)
```

## Foreign Key

``` text
module_id
    -> crud_modules.id
    ON DELETE CASCADE
```

------------------------------------------------------------------------

# 14. `crud_form_fields`

Defines how each field behaves in a specific form.

## Columns

  Column              Type              Attributes
  ------------------- ----------------- --------------------
  id                  BIGINT UNSIGNED   PK, AUTO_INCREMENT
  form_id             BIGINT UNSIGNED   FK
  field_id            BIGINT UNSIGNED   FK
  component           VARCHAR(50)       NOT NULL
  label               VARCHAR(150)      NULL
  placeholder         VARCHAR(255)      NULL
  width               INT               DEFAULT 12
  required            BOOLEAN           DEFAULT FALSE
  readonly            BOOLEAN           DEFAULT FALSE
  hidden              BOOLEAN           DEFAULT FALSE
  disabled            BOOLEAN           DEFAULT FALSE
  help_text           TEXT              NULL
  component_options   JSON              NULL
  sort_order          INT               DEFAULT 0
  created_at          TIMESTAMP         NULL
  updated_at          TIMESTAMP         NULL

## Foreign Keys

``` text
form_id
    -> crud_forms.id
    ON DELETE CASCADE

field_id
    -> crud_fields.id
    ON DELETE CASCADE
```

## Constraints

``` text
UNIQUE(form_id, field_id)
INDEX(form_id, sort_order)
```

------------------------------------------------------------------------

# 15. Form Validation

Do not store arbitrary PHP code.

Prefer structured validation metadata:

``` json
{
    "required": true,
    "string": true,
    "min": 3,
    "max": 255,
    "unique": true
}
```

The generator converts this metadata into Laravel Form Request rules.

Generated example:

``` php
public function rules(): array
{
    return [
        'category_name' => [
            'required',
            'string',
            'max:255',
            'unique:product_categories,category_name',
        ],
    ];
}
```

------------------------------------------------------------------------

# 16. `crud_lists`

Defines the generated index/list/table configuration.

## Columns

  Column               Type              Attributes
  -------------------- ----------------- --------------------
  id                   BIGINT UNSIGNED   PK, AUTO_INCREMENT
  module_id            BIGINT UNSIGNED   FK
  name                 VARCHAR(100)      NOT NULL
  pagination_enabled   BOOLEAN           DEFAULT TRUE
  default_per_page     INT               DEFAULT 25
  max_per_page         INT               DEFAULT 100
  search_enabled       BOOLEAN           DEFAULT TRUE
  sorting_enabled      BOOLEAN           DEFAULT TRUE
  filtering_enabled    BOOLEAN           DEFAULT TRUE
  config               JSON              NULL
  created_at           TIMESTAMP         NULL
  updated_at           TIMESTAMP         NULL

## Constraints

``` text
UNIQUE(module_id)
```

## Foreign Key

``` text
module_id
    -> crud_modules.id
    ON DELETE CASCADE
```

------------------------------------------------------------------------

# 17. `crud_list_columns`

Defines table columns.

## Columns

  Column              Type              Attributes
  ------------------- ----------------- --------------------
  id                  BIGINT UNSIGNED   PK, AUTO_INCREMENT
  list_id             BIGINT UNSIGNED   FK
  field_id            BIGINT UNSIGNED   NULL, FK
  key_name            VARCHAR(100)      NOT NULL
  label               VARCHAR(150)      NOT NULL
  visible             BOOLEAN           DEFAULT TRUE
  sortable            BOOLEAN           DEFAULT FALSE
  searchable          BOOLEAN           DEFAULT FALSE
  filterable          BOOLEAN           DEFAULT FALSE
  width               VARCHAR(30)       NULL
  formatter           VARCHAR(50)       NULL
  formatter_options   JSON              NULL
  sort_order          INT               DEFAULT 0
  created_at          TIMESTAMP         NULL
  updated_at          TIMESTAMP         NULL

## Foreign Keys

``` text
list_id
    -> crud_lists.id
    ON DELETE CASCADE

field_id
    -> crud_fields.id
    ON DELETE SET NULL
```

## Index

``` text
INDEX(list_id, sort_order)
```

------------------------------------------------------------------------

# 18. `crud_list_filters`

Defines filters for a list.

## Columns

  Column       Type              Attributes
  ------------ ----------------- --------------------
  id           BIGINT UNSIGNED   PK, AUTO_INCREMENT
  list_id      BIGINT UNSIGNED   FK
  field_id     BIGINT UNSIGNED   FK
  type         VARCHAR(50)       NOT NULL
  label        VARCHAR(150)      NULL
  config       JSON              NULL
  sort_order   INT               DEFAULT 0
  created_at   TIMESTAMP         NULL
  updated_at   TIMESTAMP         NULL

## Filter Types

``` text
text
number
date
date-range
select
boolean
relationship
```

## Foreign Keys

``` text
list_id
    -> crud_lists.id
    ON DELETE CASCADE

field_id
    -> crud_fields.id
    ON DELETE CASCADE
```

------------------------------------------------------------------------

# 19. `crud_list_actions`

Recommended addition for row and bulk actions.

Examples:

``` text
View
Edit
Delete
Restore
Activate
Deactivate
Export
```

## Columns

``` text
id
list_id
name
label
action_type
icon
route
permission
confirmation_required
confirmation_title
confirmation_message
configuration
display_order
is_bulk
is_active
created_at
updated_at
deleted_at
```

Suggested types:

``` text
view
edit
delete
restore
custom
export
activate
deactivate
```

------------------------------------------------------------------------

# 20. `crud_module_permissions`

If using Spatie, Spatie owns the actual authorization tables. This
builder table only defines which permissions belong to a CRUD module.

## Columns

  Column            Type              Attributes
  ----------------- ----------------- --------------------
  id                BIGINT UNSIGNED   PK, AUTO_INCREMENT
  module_id         BIGINT UNSIGNED   FK
  permission_name   VARCHAR(150)      NOT NULL
  action            VARCHAR(50)       NOT NULL
  created_at        TIMESTAMP         NULL
  updated_at        TIMESTAMP         NULL

## Constraint

``` text
UNIQUE(module_id, permission_name)
```

## Generated Permissions

``` text
product-category.view
product-category.create
product-category.update
product-category.delete
product-category.restore
product-category.export
```

Only generate actions enabled by the module configuration.

------------------------------------------------------------------------

# 21. `crud_api_configs`

Defines API behavior for a module.

## Columns

  Column                Type              Attributes
  --------------------- ----------------- --------------------
  id                    BIGINT UNSIGNED   PK, AUTO_INCREMENT
  module_id             BIGINT UNSIGNED   FK
  enabled               BOOLEAN           DEFAULT TRUE
  version               VARCHAR(20)       DEFAULT `v1`
  prefix                VARCHAR(100)      DEFAULT `api`
  generate_controller   BOOLEAN           DEFAULT TRUE
  generate_resource     BOOLEAN           DEFAULT TRUE
  generate_requests     BOOLEAN           DEFAULT TRUE
  generate_policy       BOOLEAN           DEFAULT TRUE
  pagination_enabled    BOOLEAN           DEFAULT TRUE
  search_enabled        BOOLEAN           DEFAULT TRUE
  filtering_enabled     BOOLEAN           DEFAULT TRUE
  sorting_enabled       BOOLEAN           DEFAULT TRUE
  config                JSON              NULL
  created_at            TIMESTAMP         NULL
  updated_at            TIMESTAMP         NULL

## Constraint

``` text
UNIQUE(module_id)
```

## Example API

``` text
GET     /api/v1/product-categories
POST    /api/v1/product-categories
GET     /api/v1/product-categories/{id}
PUT     /api/v1/product-categories/{id}
DELETE  /api/v1/product-categories/{id}
```

------------------------------------------------------------------------

# 22. API Response Fields

If you later need per-field API response control, add:

## `crud_api_fields`

``` text
id
endpoint/config_id
field_id
response_key
source_column
field_type
visible
nullable
transformer
transformer_config
display_order
created_at
updated_at
```

This prevents exposing every database column automatically.

------------------------------------------------------------------------

# 23. `crud_module_versions`

Stores complete configuration snapshots.

This is useful for rollback.

## Columns

  Column       Type              Attributes
  ------------ ----------------- --------------------
  id           BIGINT UNSIGNED   PK, AUTO_INCREMENT
  module_id    BIGINT UNSIGNED   FK
  version      INT               NOT NULL
  snapshot     JSON              NOT NULL
  created_by   BIGINT UNSIGNED   NULL
  created_at   TIMESTAMP         NULL

## Constraint

``` text
UNIQUE(module_id, version)
```

The snapshot can contain:

``` json
{
    "module": {},
    "fields": [],
    "relationships": [],
    "forms": [],
    "lists": [],
    "permissions": {},
    "api": {}
}
```

------------------------------------------------------------------------

# 24. `crud_generation_runs`

Every time the user clicks Generate, create a generation run.

## Columns

  Column          Type              Attributes
  --------------- ----------------- -----------------------------------
  id              BIGINT UNSIGNED   PK, AUTO_INCREMENT
  module_id       BIGINT UNSIGNED   FK
  version         INT               NOT NULL
  status          ENUM              pending, running, success, failed
  started_at      TIMESTAMP         NULL
  completed_at    TIMESTAMP         NULL
  error_message   TEXT              NULL
  generated_by    BIGINT UNSIGNED   NULL
  metadata        JSON              NULL
  created_at      TIMESTAMP         NULL
  updated_at      TIMESTAMP         NULL

## Status

``` text
pending
running
success
failed
```

## Indexes

``` text
INDEX(module_id)
INDEX(status)
```

------------------------------------------------------------------------

# 25. `crud_generation_files`

Tracks every file produced by a generation run.

## Columns

  Column              Type              Attributes
  ------------------- ----------------- -----------------------------------
  id                  BIGINT UNSIGNED   PK, AUTO_INCREMENT
  generation_run_id   BIGINT UNSIGNED   FK
  file_type           VARCHAR(50)       NOT NULL
  file_path           VARCHAR(500)      NOT NULL
  content_hash        VARCHAR(64)       NULL
  content             LONGTEXT          NULL
  status              ENUM              created, updated, skipped, failed
  created_at          TIMESTAMP         NULL
  updated_at          TIMESTAMP         NULL

## File types

``` text
migration
model
controller
request
resource
policy
route
test
frontend
config
```

## Foreign Key

``` text
generation_run_id
    -> crud_generation_runs.id
    ON DELETE CASCADE
```

------------------------------------------------------------------------

# 26. Complete Foreign-Key Map

``` text
crud_fields.module_id
    -> crud_modules.id

crud_relationships.module_id
    -> crud_modules.id

crud_relationships.related_module_id
    -> crud_modules.id

crud_forms.module_id
    -> crud_modules.id

crud_form_fields.form_id
    -> crud_forms.id

crud_form_fields.field_id
    -> crud_fields.id

crud_lists.module_id
    -> crud_modules.id

crud_list_columns.list_id
    -> crud_lists.id

crud_list_columns.field_id
    -> crud_fields.id

crud_list_filters.list_id
    -> crud_lists.id

crud_list_filters.field_id
    -> crud_fields.id

crud_list_actions.list_id
    -> crud_lists.id

crud_module_permissions.module_id
    -> crud_modules.id

crud_api_configs.module_id
    -> crud_modules.id

crud_module_versions.module_id
    -> crud_modules.id

crud_generation_runs.module_id
    -> crud_modules.id

crud_generation_files.generation_run_id
    -> crud_generation_runs.id
```

------------------------------------------------------------------------

# 27. Recommended Delete Behavior

Use cascading deletes for configuration owned by a module:

``` text
crud_modules
    |
    +-- crud_fields              CASCADE
    +-- crud_relationships       CASCADE
    +-- crud_forms               CASCADE
    +-- crud_lists               CASCADE
    +-- crud_module_permissions  CASCADE
    +-- crud_api_configs         CASCADE
    +-- crud_module_versions     CASCADE
    +-- crud_generation_runs     CASCADE
```

For relationships pointing to another module:

``` text
related_module_id
    -> ON DELETE SET NULL
```

This prevents a module relationship record from breaking when the
related module definition is removed.

------------------------------------------------------------------------

# 28. Example: Product Category

When an admin creates:

``` text
Module:
Product Category

Display:
Product Categories

Table:
product_categories

Model:
ProductCategory

API:
product-categories

Soft Delete:
Yes

Audit Log:
Yes
```

## `crud_modules`

``` text
id = 1
name = product_category
display_name = Product Category
slug = product-category
table_name = product_categories
model_name = ProductCategory
api_prefix = product-categories
```

## Fields

``` text
id    module_id    name
-----------------------------
1     1            id
2     1            category_name
3     1            description
4     1            status
```

## Forms

``` text
Create Product Category
Edit Product Category
```

## List

``` text
Product Categories

ID | Category Name | Status | Actions
```

## Permissions

``` text
product-category.view
product-category.create
product-category.update
product-category.delete
product-category.restore
product-category.export
```

------------------------------------------------------------------------

# 29. Generation Flow

The backend generation flow should be:

``` text
Create Module
     |
     v
Module Information
     |
     v
Database Fields
     |
     v
Relationships
     |
     v
Form Configuration
     |
     v
List Configuration
     |
     v
Permissions
     |
     v
API Configuration
     |
     v
Validate Configuration
     |
     v
Create Version Snapshot
     |
     v
Preview
     |
     v
User Review
     |
     v
Generate
     |
     +-- Migration
     +-- Model
     +-- Requests
     +-- Resource
     +-- Controller
     +-- Policy
     +-- Routes
     +-- Tests
     +-- Frontend
     |
     v
Store Generation Run
     |
     v
Store Generated Files
```

------------------------------------------------------------------------

# 30. Generated Laravel Files

For:

``` text
Product Category
```

generate:

``` text
app/
├── Models/
│   └── ProductCategory.php
│
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── ProductCategoryController.php
│   │
│   ├── Requests/
│   │   └── ProductCategory/
│   │       ├── StoreProductCategoryRequest.php
│   │       └── UpdateProductCategoryRequest.php
│   │
│   └── Resources/
│       └── ProductCategoryResource.php
│
└── Policies/
    └── ProductCategoryPolicy.php

database/
└── migrations/
    └── xxxx_create_product_categories_table.php

routes/
└── modules/
    └── product_category.php
```

Later, Next.js can generate:

``` text
app/
└── (dashboard)/
    └── product-categories/
        ├── page.tsx
        ├── create/
        │   └── page.tsx
        └── [id]/
            └── edit/
                └── page.tsx
```

------------------------------------------------------------------------

# 31. Review & Generate Validation

Before generation, validate:

``` text
✓ Module name valid
✓ Table name valid
✓ Model name valid
✓ API prefix valid
✓ No duplicate fields
✓ Primary key exists
✓ Field types valid
✓ Relationship targets exist
✓ Foreign keys valid
✓ Form fields valid
✓ List fields valid
✓ Permissions valid
✓ API configuration valid
```

Only enable Generate when configuration is valid.

------------------------------------------------------------------------

# 32. Recommended Backend Services

Keep generation logic out of controllers.

Recommended services:

``` text
app/Services/
├── CrudBuilder/
│   ├── ModuleService.php
│   ├── FieldService.php
│   ├── RelationshipService.php
│   ├── FormService.php
│   ├── ListService.php
│   ├── PermissionService.php
│   └── ApiService.php
│
├── Validation/
│   └── CrudConfigurationValidator.php
│
└── Generator/
    ├── CrudGenerator.php
    ├── ModelGenerator.php
    ├── MigrationGenerator.php
    ├── ControllerGenerator.php
    ├── RequestGenerator.php
    ├── ResourceGenerator.php
    ├── PolicyGenerator.php
    ├── RouteGenerator.php
    └── TestGenerator.php
```

------------------------------------------------------------------------

# 33. Stub / Template Structure

Use templates for generated code:

``` text
resources/
└── stubs/
    └── crud/
        ├── model.stub
        ├── migration.stub
        ├── controller.stub
        ├── store-request.stub
        ├── update-request.stub
        ├── resource.stub
        ├── policy.stub
        ├── route.stub
        └── test.stub
```

The generator replaces placeholders such as:

``` text
{{ model }}
{{ table }}
{{ namespace }}
{{ fillable }}
{{ relationships }}
{{ validation_rules }}
```

------------------------------------------------------------------------

# 34. Recommended V1 Scope

Do not implement everything initially.

## V1 Database Types

``` text
string
text
integer
bigInteger
decimal
boolean
date
datetime
json
foreignId
enum
```

## V1 UI Components

``` text
text
textarea
number
email
select
checkbox
switch
date
datetime
relationship-select
```

## V1 Relationships

``` text
belongsTo
hasOne
hasMany
belongsToMany
```

## V1 Features

``` text
Module
Fields
Relationships
Create/Edit Forms
List/Table
Search
Filter
Sort
Pagination
CRUD Permissions
REST API
Preview
Code Generation
Versioning
Generation History
```

------------------------------------------------------------------------

# 35. Future Tables

Do not add these until the V1 architecture is stable:

``` text
crud_webhooks
crud_imports
crud_exports
crud_dashboard_widgets
crud_workflows
crud_field_options
crud_translations
crud_custom_actions
crud_module_templates
crud_module_dependencies
```

These can be added later without changing the core architecture.

------------------------------------------------------------------------

# 36. Final Recommended Database

``` text
AUTH
├── users
├── roles
├── permissions
├── model_has_roles
├── model_has_permissions
└── role_has_permissions

BUILDER
├── crud_modules
├── crud_fields
├── crud_relationships
│
├── crud_forms
├── crud_form_fields
│
├── crud_lists
├── crud_list_columns
├── crud_list_filters
├── crud_list_actions
│
├── crud_module_permissions
│
├── crud_api_configs
│
├── crud_module_versions
├── crud_generation_runs
└── crud_generation_files
```

------------------------------------------------------------------------

# 37. Implementation Order

Build the backend in this order:

``` text
1. Laravel project
       ↓
2. MySQL configuration
       ↓
3. Sanctum
       ↓
4. Spatie Permission
       ↓
5. Builder database migrations
       ↓
6. Eloquent models
       ↓
7. Model relationships
       ↓
8. Seeders
       ↓
9. Builder API
       ↓
10. Configuration validator
       ↓
11. Naming service
       ↓
12. Generator services
       ↓
13. Stub/template system
       ↓
14. Preview API
       ↓
15. Generate API
       ↓
16. Generation history/versioning
       ↓
17. Next.js frontend
```

The database configuration should be the source of truth. The generated
Laravel/Next.js code should always be treated as an output of the
builder configuration.
