<?php

$structure = array(
			array('Name', 'string', 'not_required' => true, 'short_name' => 'display_name'),
			array('Filename', 'string', 'short_name' => 'name'),
			array('Directory', 'string', 'short_name' => 'directory'),
			array('Size', 'number', 'short_name' => 'size'),
			array('Width', 'number', 'short_name' => 'original_width'),
			array('Height', 'number', 'short_name' => 'original_height'),
			array('Type', 'string', 'short_name' => 'type'),
			array('Description', 'string', 'not_required' => true, 'short_name' => 'description'),
			array('Banner', 'yesno', 'short_name' => 'banner'),
			array('banner_x1', 'number', 'show' => array('banner' => 'Yes'), 'hide' => array('banner' => 'No')),
			array('banner_x2', 'number', 'show' => array('banner' => 'Yes'), 'hide' => array('banner' => 'No')),
			array('banner_y1', 'number', 'show' => array('banner' => 'Yes'), 'hide' => array('banner' => 'No')),
			array('banner_y2', 'number', 'show' => array('banner' => 'Yes'), 'hide' => array('banner' => 'No')),
			array('square_x1', 'number', 'show' => array('square' => 'Yes'), 'hide' => array('square' => 'No')),
			array('square_x2', 'number', 'show' => array('square' => 'Yes'), 'hide' => array('square' => 'No')),
			array('square_y1', 'number', 'show' => array('square' => 'Yes'), 'hide' => array('square' => 'No')),
			array('square_y2', 'number', 'show' => array('square' => 'Yes'), 'hide' => array('square' => 'No')),
			array('portrait_x1', 'number', 'show' => array('portrait' => 'Yes'), 'hide' => array('portrait' => 'No')),
			array('portrait_x2', 'number', 'show' => array('portrait' => 'Yes'), 'hide' => array('portrait' => 'No')),
			array('portrait_y1', 'number', 'show' => array('portrait' => 'Yes'), 'hide' => array('portrait' => 'No')),
			array('portrait_y2', 'number', 'show' => array('portrait' => 'Yes'), 'hide' => array('portrait' => 'No')),
			array('Portrait', 'yesno', 'short_name' => 'portrait'),
			array('Square', 'yesno', 'short_name' => 'square')
		);


?>