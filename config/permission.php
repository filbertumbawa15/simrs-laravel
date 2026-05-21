<?php

return [

    'models' => [

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your permissions.
         */
        'permission' => Spatie\Permission\Models\Permission::class,

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles.
         */
        'role' => Spatie\Permission\Models\Role::class,

    ],

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        /*
         * Change this if you want to name the related pivots other than defaults
         */
        'role_pivot_key' => null,
        'permission_pivot_key' => null,

        /*
         * Change this if you want to name the related model primary key other than `model_id`.
         * PENTING: Karena User pakai UUID, kita rename ke `model_uuid`.
         * Migration `create_permission_tables.php` juga sudah disesuaikan.
         */
        'model_morph_key' => 'model_uuid',

        /*
         * Change this if you want to use the teams feature and your related model's
         * foreign key is other than `team_id`.
         */
        'team_foreign_key' => 'team_id',
    ],

    /*
     * When set to true, the method assigning permissions to a role will register an audit log entry.
     */
    'register_permission_check_method' => true,

    /*
     * When set to true the package implements teams using the 'team_foreign_key'.
     * Tidak dipakai di SIHRS — RS biasanya single tenant.
     */
    'teams' => false,

    /*
     * The class to use to resolve the permissions team id
     */
    'team_resolver' => \Spatie\Permission\DefaultTeamResolver::class,

    /*
     * Passport client credentials grant
     */
    'use_passport_client_credentials' => false,

    /*
     * When set to true, the required permission names are added to exception messages.
     * Dev: true (debug lebih mudah). Production: false (jangan bocorkan struktur permission).
     */
    'display_permission_in_exception' => false,

    /*
     * When set to true, the required role names are added to exception messages.
     */
    'display_role_in_exception' => false,

    /*
     * By default wildcard permission lookups are disabled.
     */
    'enable_wildcard_permission' => false,

    /*
     * The class to use for interpreting wildcard permissions.
     */
    // 'permission.wildcard_permission' => Spatie\Permission\WildcardPermission::class,

    'cache' => [

        /*
         * By default all permissions are cached for 24 hours to speed up performance.
         * Aman untuk RS karena perubahan role jarang.
         */
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),

        /*
         * The cache key used to store all permissions.
         */
        'key' => 'spatie.permission.cache',

        /*
         * You may optionally indicate a specific cache driver to use for permission and
         * role caching using any of the `store` drivers listed in the cache.php config
         * file. Using 'default' here means to use the `default` set in cache.php.
         */
        'store' => 'default',
    ],
];
