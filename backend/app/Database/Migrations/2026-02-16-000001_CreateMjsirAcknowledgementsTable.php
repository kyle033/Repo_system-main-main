<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMjsirAcknowledgementsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'journal_title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'volume' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'issue' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'date_distributed' => [
                'type' => 'DATE',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('date_distributed');
        $this->forge->createTable('mjsir_acknowledgements');
    }

    public function down()
    {
        $this->forge->dropTable('mjsir_acknowledgements');
    }
}
