<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'property.view' => 'View properties',
            'property.create' => 'Create properties',
            'property.update' => 'Update properties',
            'property.delete' => 'Delete properties',
            'property.publish' => 'Publish properties',
            'property.reference' => 'Edit property references',
            'project.view' => 'View projects',
            'project.create' => 'Create projects',
            'project.update' => 'Update projects',
            'project.delete' => 'Delete projects',
            'project.publish' => 'Publish projects',
            'unit.view' => 'View units',
            'unit.create' => 'Create units',
            'unit.update' => 'Update units',
            'unit.delete' => 'Delete units',
            'media.create' => 'Upload media',
            'media.delete' => 'Delete media',
            'lead.view' => 'View assigned leads',
            'lead.view_all' => 'View every lead',
            'lead.update' => 'Update leads',
            'lead.assign' => 'Assign leads',
            'lead.export' => 'Export leads',
            'visit.view' => 'View site visits',
            'visit.update' => 'Update site visits',
            'staff.view' => 'View staff',
            'staff.create' => 'Create staff',
            'staff.update' => 'Update staff',
            'staff.deactivate' => 'Deactivate staff',
            'settings.update' => 'Update settings',
            'reference.manage' => 'Manage reference data',
            'cms.view' => 'View CMS',
            'cms.create' => 'Create CMS content',
            'cms.update' => 'Update CMS content',
            'cms.delete' => 'Delete CMS content',
            'cms.publish' => 'Publish CMS content',
            'audit.view' => 'View the audit log',
            'redirect.manage' => 'Manage redirects',
            'report.view' => 'View KPI reports',
        ];

        $permissionIds = [];
        foreach ($permissions as $key => $label) {
            $permissionIds[$key] = Permission::query()->updateOrCreate(['key' => $key], ['label' => $label])->id;
        }

        Permission::query()->whereNotIn('key', array_keys($permissions))->delete();

        $roles = [
            Role::OWNER_ADMIN => [
                'label' => 'Owner administrator',
                'permissions' => array_keys($permissions),
            ],
            Role::CONTENT_EDITOR => [
                'label' => 'Content editor',
                'permissions' => [
                    'property.view', 'property.create', 'property.update',
                    'project.view', 'project.create', 'project.update',
                    'unit.view', 'unit.create', 'unit.update', 'unit.delete',
                    'media.create', 'media.delete',
                    'cms.view', 'cms.create', 'cms.update',
                    'reference.manage',
                ],
            ],
            Role::SALES_USER => [
                'label' => 'Sales user',
                'permissions' => [
                    'property.view', 'project.view',
                    'lead.view', 'lead.update',
                    'visit.view', 'visit.update',
                ],
            ],
        ];

        foreach ($roles as $key => $definition) {
            $role = Role::query()->updateOrCreate(['key' => $key], ['label' => $definition['label']]);
            $role->permissions()->sync(array_map(fn (string $permission) => $permissionIds[$permission], $definition['permissions']));
        }
    }
}
