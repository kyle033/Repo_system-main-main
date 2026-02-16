<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateMjsirAcknowledgementsFields extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('mjsir_acknowledgements', [
            'journal_title' => [
                'name'       => 'journal_title',
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
        ]);

        $this->forge->addColumn('mjsir_acknowledgements', [
            'distributed_time' => [
                'type' => 'TIME',
                'null' => true,
                'after' => 'date_distributed',
            ],
            'recipient_name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'distributed_time',
            ],
            'position' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'recipient_name',
            ],
            'affiliation_agency' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'position',
            ],
            'no_of_copies' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'issue',
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'no_of_copies',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('mjsir_acknowledgements', 'distributed_time');
        $this->forge->dropColumn('mjsir_acknowledgements', 'recipient_name');
        $this->forge->dropColumn('mjsir_acknowledgements', 'position');
        $this->forge->dropColumn('mjsir_acknowledgements', 'affiliation_agency');
        $this->forge->dropColumn('mjsir_acknowledgements', 'no_of_copies');
        $this->forge->dropColumn('mjsir_acknowledgements', 'remarks');

        $this->forge->modifyColumn('mjsir_acknowledgements', [
            'journal_title' => [
                'name'       => 'journal_title',
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
        ]);
    }
}
