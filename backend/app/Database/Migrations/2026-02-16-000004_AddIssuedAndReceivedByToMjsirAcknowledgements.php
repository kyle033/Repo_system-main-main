<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIssuedAndReceivedByToMjsirAcknowledgements extends Migration
{
    public function up()
    {
        $this->forge->addColumn('mjsir_acknowledgements', [
            'issued_by' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'affiliation_agency',
            ],
            'received_by' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'issued_by',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('mjsir_acknowledgements', 'issued_by');
        $this->forge->dropColumn('mjsir_acknowledgements', 'received_by');
    }
}
