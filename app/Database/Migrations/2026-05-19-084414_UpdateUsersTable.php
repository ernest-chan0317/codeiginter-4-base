<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateUsersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'display_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'after'      => 'username',
            ],
            'user_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'after'      => 'display_name',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'display_name');
        $this->forge->dropColumn('users', 'user_email');
    }
}
