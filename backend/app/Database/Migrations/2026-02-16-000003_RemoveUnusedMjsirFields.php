<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveUnusedMjsirFields extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fields = array_map('strtolower', $db->getFieldNames('mjsir_acknowledgements'));

        if (in_array('journal_title', $fields, true)) {
            $this->forge->dropColumn('mjsir_acknowledgements', 'journal_title');
        }
        if (in_array('issue', $fields, true)) {
            $this->forge->dropColumn('mjsir_acknowledgements', 'issue');
        }
        if (in_array('notes', $fields, true)) {
            $this->forge->dropColumn('mjsir_acknowledgements', 'notes');
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $fields = array_map('strtolower', $db->getFieldNames('mjsir_acknowledgements'));

        if (!in_array('journal_title', $fields, true)) {
            $this->forge->addColumn('mjsir_acknowledgements', [
                'journal_title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'null'       => true,
                ],
            ]);
        }
        if (!in_array('issue', $fields, true)) {
            $this->forge->addColumn('mjsir_acknowledgements', [
                'issue' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'null'       => true,
                ],
            ]);
        }
        if (!in_array('notes', $fields, true)) {
            $this->forge->addColumn('mjsir_acknowledgements', [
                'notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);
        }
    }
}
