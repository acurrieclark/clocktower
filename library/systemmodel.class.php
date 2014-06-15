<?php

/**
* System Model Class extending the basic Model
*/

class systemModel extends model
{

	function structure() {
		$model_name = get_class($this);
		if (file_exists (ROOT . DS . 'library' . DS . 'structures' . DS . $model_name . '.php')) {
			require (ROOT . DS . 'library' . DS . 'structures' . DS . $model_name . '.php');
		}
		$model_structure = $structure;
		if (file_exists (ROOT . DS . 'application' . DS . 'structures' . DS . 'user.php') && $model_name == 'user') {
			require (ROOT . DS . 'application' . DS . 'structures' . DS . 'user.php');
			$model_structure = array_merge($model_structure, $structure);
		}


		if ($model_structure) {
			return $model_structure;

		}
		else return false;
	}

}



?>
