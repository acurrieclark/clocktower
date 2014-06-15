<?php

class image extends systemModel
{	

	function remove_versions() {
		$path = ROOT.'/public/files/images/'.$this->directory;
		$results = scandir($path);
	
		foreach ($results as $result) {
		    if ($result === '.' or $result === '..') continue;
	
		    if (is_dir($path . '/' . $result)) {
				if (file_exists($path.DS.$result.DS.$this->name)) {
					unlink($path.DS.$result.DS.$this->name);
				}
		    }
		}
	}
	
	function url($version = '') {
		if ($version != 'original' && $version) {
			$version = DS . $version;
		}
		else if ($version)
			$version = DS . 'large';
		return ABSOLUTE . 'files/images/' . $this->directory->value . $version . DS . $this->name;
	}

}

?>