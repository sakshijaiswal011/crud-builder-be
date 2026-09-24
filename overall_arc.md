1. Overall architecture
Use two conceptual layers.

                    CRUD BUILDER
                         |
        +----------------+----------------+
        |                                 |
        v                                 v
  Builder Metadata DB              Generated Application
        |                                 |
        |                                 +-- migrations
        |                                 +-- Models
        |                                 +-- Controllers
        |                                 +-- Requests
        |                                 +-- Resources
        |                                 +-- Policies
        |                                 +-- Routes
        |                                 +-- Next.js pages
        |
        +-- modules
        +-- fields
        +-- relationships
        +-- forms
        +-- lists
        +-- permissions
        +-- API configuration
        +-- generation history
For example, when the admin creates:

Module:
Product Category

Table:
product_categories

Fields:
id
category_name
description
status
your builder stores this definition in its own tables.

Then:

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
2. Your 8 steps
I would make your wizard exactly like this.

Step	Name	Main purpose
1	Module Information	Define module/table/API identity
2	Database Fields	Define columns
3	Relationships	Define Eloquent relationships
4	Form Configuration	Define create/edit UI metadata
5	List Configuration	Define index/table UI metadata
6	Permissions	Define CRUD authorization
7	API & Frontend	Define API/frontend generation
8	Review & Generate	Validate and generate
But internally I would expand each step slightly.

3. Step 1 — Module Information
This is the root record.

UI fields
Module Name
Display Name
Table Name
Model Name
API Prefix
Route Prefix
Menu Label
Menu Icon
Menu Group

Soft Delete
Audit Log
Status

Example:

Module Name:       Product Category
Display Name:      Product Categories

Table Name:        product_categories
Model Name:        ProductCategory

API Prefix:        product-categories
Route Prefix:      product-categories

Menu Label:        Product Categories
Menu Group:        Catalog

Soft Delete:       Yes
Audit Log:         Yes
Important generated names
Don't let the frontend provide arbitrary PHP class names.

Calculate them on the backend:

product_category
        |
        +-- ProductCategory
        +-- product_categories
        +-- product-categories
        +-- Product Categories
Create a naming service:

ModuleNamingService
For example:

$names = $namingService->generate('Product Category');
Returns:

[
    'singular' => 'product_category',
    'plural' => 'product_categories',
    'model' => 'ProductCategory',
    'table' => 'product_categories',
    'route' => 'product-categories',
    'api' => 'product-categories',
    'label' => 'Product Category',
]
This prevents a lot of generator bugs.

4. Step 2 — Database Fields
This is the most important part of your builder.

Your screenshot already has:

Field Name
Type
Length
Nullable
Default
Index
Unique
I would add more.

Field configuration
Field Name
Database Type
Length
Precision
Scale

Nullable
Default

Primary Key
Auto Increment
Unsigned

Unique
Index
Index Name

Enum Values

Comment

Foreign Key
Referenced Table
Referenced Column
On Delete
On Update
Supported types
Start with:

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
Don't implement 40 types initially.

Start with approximately 15.

5. Field-level application configuration
Database information alone isn't enough.

A field also needs UI/validation metadata.

For example:

{
    "name": "category_name",
    "type": "string",
    "length": 255,
    "nullable": false,
    "unique": true,
    "validation": {
        "required": true,
        "max": 255
    },
    "form": {
        "component": "text",
        "label": "Category Name",
        "placeholder": "Enter category name"
    },
    "list": {
        "visible": true,
        "sortable": true,
        "searchable": true
    }
}
This becomes extremely useful later.

6. Step 3 — Relationships
This deserves its own table.

Support:

belongsTo
hasOne
hasMany
belongsToMany
morphTo
morphMany
For your first version, implement:

belongsTo
hasOne
hasMany
belongsToMany
Example:

Product belongsTo ProductCategory

ProductCategory hasMany Products
UI:

Relationship Name
Type
Related Module
Foreign Key
Local Key
Pivot Table
Pivot Foreign Key
Related Pivot Foreign Key
On Delete
Example:

Name: category

Type: belongsTo

Related Module: Product Category

Foreign Key: category_id

Owner Key: id
Generator creates:

public function category()
{
    return $this->belongsTo(ProductCategory::class, 'category_id');
}
7. Step 4 — Form Configuration
This should determine how Next.js renders create/edit forms.

Do not store only:

field = category_name
Store presentation metadata.

Example:

{
    "field": "category_name",
    "component": "text",
    "label": "Category Name",
    "placeholder": "Enter category name",
    "required": true,
    "readonly": false,
    "hidden": false,
    "width": 6,
    "order": 1
}
Components
Start with:

text
textarea
number
email
password
select
radio
checkbox
switch
date
datetime
file
image
richtext
relationship-select
8. Form validation
This is important.

Your builder should store validation metadata.

Example:

{
    "rules": [
        "required",
        "string",
        "max:255",
        "unique:product_categories,category_name"
    ]
}
But do not allow arbitrary PHP code in validation configuration.

Instead store structured rules:

{
    "required": true,
    "string": true,
    "min": 3,
    "max": 255,
    "unique": true
}
Then your generator converts it to Laravel validation rules.

Laravel's Form Request system is a good fit for the generated requests; Laravel supports dedicated request classes containing authorization and validation rules. 
L
Laravel
+1

Generated:

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
9. Step 5 — List Configuration
This defines the generated index/list page.

Your database should know:

Column
Label
Visible
Order
Sortable
Searchable
Filterable
Width
Alignment
Formatter
Example:

{
    "field": "category_name",
    "label": "Category",
    "visible": true,
    "sortable": true,
    "searchable": true,
    "filterable": true,
    "order": 1
}
For example:

Product Categories

+----+----------------+--------+----------+
| ID | Category Name  | Status | Actions  |
+----+----------------+--------+----------+
| 1  | Electronics   | Active | Edit ... |
| 2  | Furniture     | Active | Edit ... |
+----+----------------+--------+----------+
10. List filters
Support:

text
number
date
date-range
select
boolean
relationship
Example:

{
    "field": "status",
    "type": "select",
    "label": "Status",
    "options": [
        {
            "label": "Active",
            "value": "active"
        },
        {
            "label": "Inactive",
            "value": "inactive"
        }
    ]
}
11. Step 6 — Permissions
Don't create a permission table specific to every module if you don't need to.

Use permission records such as:

product-category.view
product-category.create
product-category.update
product-category.delete
product-category.restore
product-category.export
For every generated module you can automatically create these.

Example:

product-category.view
product-category.create
product-category.update
product-category.delete
Then assign those permissions to roles.

I strongly recommend using Spatie Laravel Permission rather than implementing role/permission mechanics yourself. It integrates permissions with Laravel's Gate authorization system. 
S
Spatie
+1

12. Step 7 — API & Frontend
Since you specifically want Laravel + Next.js, I'd make this step mostly API configuration on the backend.

Fields:

Generate API Controller
Generate API Resource
Generate Requests
Generate Policy

API Version
API Prefix

Pagination Enabled
Default Per Page
Maximum Per Page

Search Enabled
Sorting Enabled
Filtering Enabled

Frontend Generation Enabled
Example:

API version: v1

Prefix:
api/v1

Endpoint:
api/v1/product-categories
Generated endpoints:

GET     /api/v1/product-categories
POST    /api/v1/product-categories
GET     /api/v1/product-categories/{id}
PUT     /api/v1/product-categories/{id}
DELETE  /api/v1/product-categories/{id}
Laravel resource controllers are naturally suited to this CRUD pattern. 
L
Laravel

13. Step 8 — Review & Generate
This page should not just display generated files.

Before generation, run a validator.

For example:

✓ Module name valid
✓ Table name valid
✓ Model name valid
✓ No duplicate fields
✓ Primary key exists
✓ Relationship targets exist
✓ Foreign keys valid
✓ Form fields valid
✓ List fields valid
✓ Permissions valid
✓ API configuration valid
Then:

[ Generate ]
Generation should happen as a transaction/workflow:

Validate
   ↓
Create/Update database migration
   ↓
Generate Model
   ↓
Generate Requests
   ↓
Generate Resource
   ↓
Generate Controller
   ↓
Generate Policy
   ↓
Generate Routes
   ↓
Generate Tests
   ↓
Generate frontend metadata/code
   ↓
Store generation version
14. The most important part — Builder DB structure
I recommend approximately these tables.

users
roles
permissions

crud_modules
crud_fields
crud_relationships

crud_form_configs
crud_form_fields

crud_list_configs
crud_list_columns
crud_list_filters

crud_module_permissions

crud_api_configs

crud_generation_runs
crud_generation_files
crud_module_versions
You don't necessarily need every table on day one.

15. crud_modules
This is the root table.

CREATE TABLE crud_modules (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,

    display_name VARCHAR(150) NOT NULL,

    table_name VARCHAR(150) NOT NULL UNIQUE,
    model_name VARCHAR(150) NOT NULL,

    api_prefix VARCHAR(150) NULL,
    route_prefix VARCHAR(150) NULL,

    menu_label VARCHAR(150) NULL,
    menu_group VARCHAR(100) NULL,
    menu_icon VARCHAR(100) NULL,

    description TEXT NULL,

    soft_delete BOOLEAN NOT NULL DEFAULT FALSE,
    audit_log BOOLEAN NOT NULL DEFAULT FALSE,

    status ENUM('draft','active','disabled') NOT NULL DEFAULT 'draft',

    sort_order INT NOT NULL DEFAULT 0,

    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_modules_status (status),
    INDEX idx_modules_menu_group (menu_group)
);
I would use:

name = product_category
display_name = Product Category
slug = product-category
rather than relying on display name everywhere.

16. crud_fields
This is your most important metadata table.

CREATE TABLE crud_fields (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    module_id BIGINT UNSIGNED NOT NULL,

    name VARCHAR(100) NOT NULL,
    label VARCHAR(150) NULL,

    type VARCHAR(50) NOT NULL,

    length INT NULL,
    precision_value INT NULL,
    scale_value INT NULL,

    nullable BOOLEAN NOT NULL DEFAULT FALSE,
    default_value TEXT NULL,

    is_primary BOOLEAN NOT NULL DEFAULT FALSE,
    is_auto_increment BOOLEAN NOT NULL DEFAULT FALSE,
    is_unsigned BOOLEAN NOT NULL DEFAULT FALSE,

    is_unique BOOLEAN NOT NULL DEFAULT FALSE,
    is_indexed BOOLEAN NOT NULL DEFAULT FALSE,

    index_name VARCHAR(150) NULL,

    enum_values JSON NULL,

    comment TEXT NULL,

    validation_rules JSON NULL,
    options JSON NULL,

    sort_order INT NOT NULL DEFAULT 0,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_crud_fields_module
        FOREIGN KEY (module_id)
        REFERENCES crud_modules(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_module_field (module_id, name),

    INDEX idx_fields_module_order (module_id, sort_order)
);
17. Why options JSON?
Because your builder will eventually have hundreds of configuration possibilities.

Instead of continuously changing the database:

crud_fields
    |
    +-- placeholder
    +-- mask
    +-- prefix
    +-- suffix
    +-- autocomplete
    +-- searchable
    +-- etc.
you can have:

{
    "placeholder": "Enter category name",
    "searchable": true,
    "autocomplete": false,
    "formatter": "text"
}
But don't put core relational information into JSON.

For example, don't do:

{
   "foreign_key": "category_id"
}
in JSON.

Use crud_relationships for that.

18. crud_relationships
CREATE TABLE crud_relationships (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    module_id BIGINT UNSIGNED NOT NULL,

    name VARCHAR(100) NOT NULL,

    type ENUM(
        'belongsTo',
        'hasOne',
        'hasMany',
        'belongsToMany',
        'morphTo',
        'morphMany'
    ) NOT NULL,

    related_module_id BIGINT UNSIGNED NULL,

    foreign_key VARCHAR(100) NULL,
    local_key VARCHAR(100) NULL,

    related_key VARCHAR(100) NULL,

    pivot_table VARCHAR(150) NULL,
    pivot_foreign_key VARCHAR(100) NULL,
    pivot_related_key VARCHAR(100) NULL,

    on_delete VARCHAR(30) NULL,
    on_update VARCHAR(30) NULL,

    options JSON NULL,

    sort_order INT NOT NULL DEFAULT 0,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (module_id)
        REFERENCES crud_modules(id)
        ON DELETE CASCADE,

    FOREIGN KEY (related_module_id)
        REFERENCES crud_modules(id)
        ON DELETE SET NULL
);
19. Form configuration
I would use two tables.

crud_forms
CREATE TABLE crud_forms (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    module_id BIGINT UNSIGNED NOT NULL,

    name VARCHAR(100) NOT NULL,

    type ENUM(
        'create',
        'edit'
    ) NOT NULL,

    layout ENUM(
        'single',
        'two_column',
        'three_column'
    ) NOT NULL DEFAULT 'two_column',

    config JSON NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (module_id)
        REFERENCES crud_modules(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_module_form_type (module_id, type)
);
20. crud_form_fields
CREATE TABLE crud_form_fields (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    form_id BIGINT UNSIGNED NOT NULL,
    field_id BIGINT UNSIGNED NOT NULL,

    component VARCHAR(50) NOT NULL,

    label VARCHAR(150) NULL,
    placeholder VARCHAR(255) NULL,

    width INT NOT NULL DEFAULT 12,

    required BOOLEAN NOT NULL DEFAULT FALSE,
    readonly BOOLEAN NOT NULL DEFAULT FALSE,
    hidden BOOLEAN NOT NULL DEFAULT FALSE,

    disabled BOOLEAN NOT NULL DEFAULT FALSE,

    help_text TEXT NULL,

    component_options JSON NULL,

    sort_order INT NOT NULL DEFAULT 0,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (form_id)
        REFERENCES crud_forms(id)
        ON DELETE CASCADE,

    FOREIGN KEY (field_id)
        REFERENCES crud_fields(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_form_field (form_id, field_id),

    INDEX idx_form_fields_order (form_id, sort_order)
);
21. List configuration
crud_lists
CREATE TABLE crud_lists (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    module_id BIGINT UNSIGNED NOT NULL,

    name VARCHAR(100) NOT NULL,

    pagination_enabled BOOLEAN NOT NULL DEFAULT TRUE,

    default_per_page INT NOT NULL DEFAULT 25,

    max_per_page INT NOT NULL DEFAULT 100,

    search_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    sorting_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    filtering_enabled BOOLEAN NOT NULL DEFAULT TRUE,

    config JSON NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (module_id)
        REFERENCES crud_modules(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_module_list (module_id)
);
22. crud_list_columns
CREATE TABLE crud_list_columns (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    list_id BIGINT UNSIGNED NOT NULL,
    field_id BIGINT UNSIGNED NULL,

    key_name VARCHAR(100) NOT NULL,

    label VARCHAR(150) NOT NULL,

    visible BOOLEAN NOT NULL DEFAULT TRUE,
    sortable BOOLEAN NOT NULL DEFAULT FALSE,
    searchable BOOLEAN NOT NULL DEFAULT FALSE,
    filterable BOOLEAN NOT NULL DEFAULT FALSE,

    width VARCHAR(30) NULL,

    formatter VARCHAR(50) NULL,

    formatter_options JSON NULL,

    sort_order INT NOT NULL DEFAULT 0,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (list_id)
        REFERENCES crud_lists(id)
        ON DELETE CASCADE,

    FOREIGN KEY (field_id)
        REFERENCES crud_fields(id)
        ON DELETE SET NULL,

    INDEX idx_list_columns_order (list_id, sort_order)
);
23. crud_list_filters
CREATE TABLE crud_list_filters (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    list_id BIGINT UNSIGNED NOT NULL,
    field_id BIGINT UNSIGNED NOT NULL,

    type VARCHAR(50) NOT NULL,

    label VARCHAR(150) NULL,

    config JSON NULL,

    sort_order INT NOT NULL DEFAULT 0,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (list_id)
        REFERENCES crud_lists(id)
        ON DELETE CASCADE,

    FOREIGN KEY (field_id)
        REFERENCES crud_fields(id)
        ON DELETE CASCADE
);
24. API configuration
CREATE TABLE crud_api_configs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    module_id BIGINT UNSIGNED NOT NULL,

    enabled BOOLEAN NOT NULL DEFAULT TRUE,

    version VARCHAR(20) NOT NULL DEFAULT 'v1',

    prefix VARCHAR(100) NOT NULL DEFAULT 'api',

    generate_controller BOOLEAN NOT NULL DEFAULT TRUE,
    generate_resource BOOLEAN NOT NULL DEFAULT TRUE,
    generate_requests BOOLEAN NOT NULL DEFAULT TRUE,
    generate_policy BOOLEAN NOT NULL DEFAULT TRUE,

    pagination_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    search_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    filtering_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    sorting_enabled BOOLEAN NOT NULL DEFAULT TRUE,

    config JSON NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (module_id)
        REFERENCES crud_modules(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_api_module (module_id)
);
25. Permissions
If using Spatie, let Spatie own the actual:

roles
permissions
model_has_roles
model_has_permissions
role_has_permissions
Your builder only needs to know which permissions belong to a module.

For example:

CREATE TABLE crud_module_permissions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    module_id BIGINT UNSIGNED NOT NULL,

    permission_name VARCHAR(150) NOT NULL,

    action VARCHAR(50) NOT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (module_id)
        REFERENCES crud_modules(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_module_permission (
        module_id,
        permission_name
    )
);
Automatically create:

product-category.view
product-category.create
product-category.update
product-category.delete
product-category.restore
product-category.export
depending on configuration.

26. Generation history
This is something I strongly recommend.

Your builder will eventually have:

Product Category v1
Product Category v2
Product Category v3
Don't overwrite everything without tracking it.

Create:

CREATE TABLE crud_generation_runs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    module_id BIGINT UNSIGNED NOT NULL,

    version INT NOT NULL,

    status ENUM(
        'pending',
        'running',
        'success',
        'failed'
    ) NOT NULL DEFAULT 'pending',

    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,

    error_message TEXT NULL,

    generated_by BIGINT UNSIGNED NULL,

    metadata JSON NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (module_id)
        REFERENCES crud_modules(id)
        ON DELETE CASCADE,

    INDEX idx_generation_module (module_id),
    INDEX idx_generation_status (status)
);
27. Generated files
Then:

CREATE TABLE crud_generation_files (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    generation_run_id BIGINT UNSIGNED NOT NULL,

    file_type VARCHAR(50) NOT NULL,

    file_path VARCHAR(500) NOT NULL,

    content_hash VARCHAR(64) NULL,

    content LONGTEXT NULL,

    status ENUM(
        'created',
        'updated',
        'skipped',
        'failed'
    ) NOT NULL DEFAULT 'created',

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (generation_run_id)
        REFERENCES crud_generation_runs(id)
        ON DELETE CASCADE,

    INDEX idx_generation_files_run (generation_run_id)
);
This gives you an enormous advantage later.

You can show:

Generation #12

✓ Model
✓ Migration
✓ Controller
✓ Resource
✓ Store Request
✓ Update Request
✓ Policy
✓ Routes
✓ Tests

Generated: 9 files
28. Module versions
I'd additionally have:

CREATE TABLE crud_module_versions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    module_id BIGINT UNSIGNED NOT NULL,

    version INT NOT NULL,

    snapshot JSON NOT NULL,

    created_by BIGINT UNSIGNED NULL,

    created_at TIMESTAMP NULL,

    FOREIGN KEY (module_id)
        REFERENCES crud_modules(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_module_version (
        module_id,
        version
    )
);
The snapshot contains the entire module definition:

{
    "module": {},
    "fields": [],
    "relationships": [],
    "forms": [],
    "lists": [],
    "permissions": {},
    "api": {}
}
This gives you rollback.

29. Your final builder database
Conceptually:

users
 |
 +-- created_by

crud_modules
 |
 +-- crud_fields
 |
 +-- crud
