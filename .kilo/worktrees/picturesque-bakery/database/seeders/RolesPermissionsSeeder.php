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
            'lead.view' => 'View leads',
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
            'cms.create' => 'Create CMS pages',
            'cms.update' => 'Update CMS pages',
            'cms.delete' => 'Delete CMS pages',
        ];

        $permissionIds = [];
        foreach ($permissions as $key => $label) {
            $permissionIds[$key] = Permission::query()->updateOrCreate(['key' => $key], ['label' => $label])->id;
        }

        $roles = [
            Role::OWNER_ADMIN => [
                'label' => 'Owner administrator',
                'permissions' => array_keys($permissions),
            ],
            Role::CONTENT_EDITOR => [
                'label' => 'Content editor',
                'permissions' => [
                    'property.view', 'property.create', 'property.update', 'property.delete', 'property.publish',
                    'project.view', 'project.create', 'project.update', 'project.delete', 'project.publish',
                    'unit.view', 'unit.create', 'unit.update', 'unit.delete',
                    'media.create', 'media.delete',
                    'cms.view', 'cms.create', 'cms.update', 'cms.delete',
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
