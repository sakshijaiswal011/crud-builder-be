``` text
    CREATE TABLE crud_modules (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        table_name VARCHAR(150) NOT NULL UNIQUE,
        api_prefix VARCHAR(150) NULL,

        menu_name VARCHAR(100) NULL,
        menu_group VARCHAR(100) NULL,
        menu_icon VARCHAR(100) NULL,

        soft_delete BOOLEAN NOT NULL DEFAULT FALSE,
        audit_log BOOLEAN NOT NULL DEFAULT FALSE,

        status ENUM('draft','active','inactive') NOT NULL DEFAULT 'draft',
        
        -- Generated features
        generate_api_controller_routes BOOLEAN NOT NULL DEFAULT TRUE,
        generate_api_resource BOOLEAN NOT NULL DEFAULT TRUE,
        generate_policy BOOLEAN NOT NULL DEFAULT TRUE,
        generate_frontend_views BOOLEAN NOT NULL DEFAULT TRUE,

        -- List configuration
        list_pagination BOOLEAN NOT NULL DEFAULT TRUE,
        list_default_per_page SMALLINT UNSIGNED NOT NULL DEFAULT 25,
        list_max_per_page SMALLINT UNSIGNED NOT NULL DEFAULT 100,

        deleted_at
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,

        UNIQUE KEY uq_crud_modules_slug (slug),
        UNIQUE KEY uq_crud_modules_table_name (table_name),

        INDEX idx_modules_status (status),
        INDEX idx_modules_menu_group (menu_group)
    );
```

---------------------------------------------------------------------------------------------
``` text
    CREATE TABLE crud_fields (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        module_id BIGINT UNSIGNED NOT NULL,

        field_name VARCHAR(100) NOT NULL,
        type VARCHAR(50) NOT NULL,
        length INT NULL,
        nullable BOOLEAN NOT NULL DEFAULT FALSE,
        default_value TEXT NULL,
        -- auto_increment BOOLEAN NOT NULL DEFAULT FALSE,
        -- is_primary BOOLEAN NOT NULL DEFAULT FALSE,
        is_unique BOOLEAN NOT NULL DEFAULT FALSE,
        is_indexed BOOLEAN NOT NULL DEFAULT FALSE,
        comment TEXT NULL,

        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,

        CONSTRAINT fk_crud_fields_module
            FOREIGN KEY (module_id)
            REFERENCES crud_modules(id)
            ON DELETE CASCADE,

        UNIQUE KEY uq_module_field (module_id, name),

        INDEX idx_fields_module_order (module_id)
    );
```

---------------------------------------------------------------------------------------------
``` text
    CREATE TABLE crud_relationships (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        module_id BIGINT UNSIGNED NOT NULL,

        relation_type ENUM(
            'hasOne',
            'hasMany',
            'belongsTo',
            'belongsToMany'
        ) NOT NULL,

        related_module_id BIGINT UNSIGNED NULL,

        foreign_key VARCHAR(100) NULL,
        local_key VARCHAR(100) NULL,

        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
    );
```

---------------------------------------------------------------------------------------------
``` text
    CREATE TABLE crud_forms_list (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

        module_id BIGINT UNSIGNED NOT NULL,
        field_id

        form_input_type VARCHAR(100) NOT NULL,
        form_label VARCHAR(100) NOT NULL,
        form_placeholder VARCHAR(100) NOT NULL,
        is_required BOOLEAN NOT NULL DEFAULT FALSE,
        validation_rules json NOT NULL, 

        -- list
        list_label VARCHAR(150) NULL,
        search_enabled BOOLEAN NOT NULL DEFAULT TRUE,
        sorting_enabled BOOLEAN NOT NULL DEFAULT TRUE,
        filtering_enabled BOOLEAN NOT NULL DEFAULT TRUE,
        width INT NOT NULL DEFAULT 25,

        deleted_at TIMESTAMP NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,

        FOREIGN KEY (module_id)
            REFERENCES crud_modules(id)
            ON DELETE CASCADE,

        UNIQUE KEY uq_module_form_type (module_id, type)
    );
```
---------------------------------------------------------------------------------------------
``` text
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
```

---------------------------------------------------------------------------------------------
``` text
    CREATE TABLE crud_module_permissions (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

        module_id BIGINT UNSIGNED NOT NULL,

        permission_name VARCHAR(150) NOT NULL,

        action VARCHAR(50) NOT NULL,

        enabled

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
```

---------------------------------------------------------------------------------------------
``` text
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
```