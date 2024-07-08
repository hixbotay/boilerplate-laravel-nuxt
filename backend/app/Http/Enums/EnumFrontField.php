<?php
namespace App\Http\Enums; 

class EnumFrontField extends Enum {
    const CATEGORY = ['display'=>'Category', 'value'=>'1'];
	const TEXT = ['display'=>'Text', 'value'=>'2'];
    const EDITOR = ['display'=>'Editor', 'value'=>'3'];
}