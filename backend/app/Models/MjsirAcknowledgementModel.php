<?php

namespace App\Models;

use CodeIgniter\Model;

class MjsirAcknowledgementModel extends Model
{
    protected $table            = 'mjsir_acknowledgements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'date_distributed',
        'distributed_time',
        'recipient_name',
        'position',
        'affiliation_agency',
        'issued_by',
        'received_by',
        'volume',
        'no_of_copies',
        'remarks',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
