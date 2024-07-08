<?php
namespace App\Http\Enums; 

class OrderStatus extends Enum {
    const PENDING = ['display'=>'Pending', 'value'=>'1'];
	const PROCESSING = ['display'=>'Processing', 'value'=>'2'];
    const FINISHED = ['display'=>'Finished', 'value'=>'3'];
    const FAILED = ['display'=>'Failed', 'value'=>'4'];
}