<?php
namespace Therac\Xdebug;

class Base {
    protected $Therac;

    private $transaction_id;
    private $break_points = [];

    public function __construct($xdebugConn, $Therac) {
        $this->Therac = $Therac;
        $xdebugConn->on('connection', function($conn) {
            $transaction_id = 0;

            $conn->on('data', function ($input) {
                var_dump($input);
            });
        });
    }
}
