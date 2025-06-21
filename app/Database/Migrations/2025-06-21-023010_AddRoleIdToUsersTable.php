<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleIdToUsersTable extends Migration
{
    public function up()
    {
        // 1. Add role_id column
        $this->forge->addColumn('users', [
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'password' // Or another suitable column
            ]
        ]);

        // 2. Data Update: Populate role_id based on existing string 'role'
        // Ensure RoleSeeder has run or roles 'admin' and 'staff' exist
        $db = \Config\Database::connect();

        $adminRole = $db->table('roles')->select('id')->where('name', 'admin')->get()->getRowArray();
        $staffRole = $db->table('roles')->select('id')->where('name', 'staff')->get()->getRowArray();

        if ($adminRole) {
            $db->table('users')->where('role', 'admin')->update(['role_id' => $adminRole['id']]);
        }
        if ($staffRole) {
            $db->table('users')->where('role', 'staff')->update(['role_id' => $staffRole['id']]);
        }
        // Any users with other roles or NULL role will have NULL role_id if not handled

        // 3. Add foreign key constraint
        // Using SQL for potentially more robust FK creation if issues with forge on existing tables
        // $this->db->query('ALTER TABLE `users` ADD CONSTRAINT `fk_users_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL ON UPDATE NO ACTION');
        // Or using Forge:
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'NO ACTION', 'SET NULL');


        // 4. Drop the old 'role' (VARCHAR) column
        $this->forge->dropColumn('users', 'role');
    }

    public function down()
    {
        // 1. Add back the old 'role' column
        $this->forge->addColumn('users', [
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => '100', // Match original constraint if known, e.g., from UserSeeder or previous migration
                'null'       => true, // Or false if it was not nullable
                'after'      => 'password' // Or original position
            ]
        ]);

        // 2. Data Update: Populate string 'role' based on 'role_id'
        $db = \Config\Database::connect();
        $roles = $db->table('roles')->select('id, name')->get()->getResultArray();
        foreach ($roles as $role) {
            $db->table('users')->where('role_id', $role['id'])->update(['role' => $role['name']]);
        }

        // 3. Drop foreign key
        // Ensure you know the constraint name if not using CI default. CI default is usually tablename_columnname_foreign
        // $this->db->query('ALTER TABLE `users` DROP FOREIGN KEY `fk_users_role_id`'); // If named manually
        $this->forge->dropForeignKey('users', 'users_role_id_foreign'); // Attempt to drop by CI default naming

        // 4. Drop role_id column
        $this->forge->dropColumn('users', 'role_id');
    }
}
