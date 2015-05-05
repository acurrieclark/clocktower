<?php


##############################################
# Output colorized text to terminal run
# php scripts..
##############################################
function echoc($text, $color="NORMAL", $back=0){
	$_colors = array(
	        'LIGHT_RED'      => "[1;31m",
	        'LIGHT_GREEN'     => "[1;32m",
	        'YELLOW'         => "[1;33m",
	        'LIGHT_BLUE'     => "[1;34m",
	        'MAGENTA'     => "[1;35m",
	        'LIGHT_CYAN'     => "[1;36m",
	        'WHITE'         => "[1;37m",
	        'NORMAL'         => "[0m",
	        'BLACK'         => "[0;30m",
	        'RED'         => "[0;31m",
	        'GREEN'         => "[0;32m",
	        'BROWN'         => "[0;33m",
	        'BLUE'         => "[0;34m",
	        'CYAN'         => "[0;36m",
	        'BOLD'         => "[1m",
	        'UNDERSCORE'     => "[4m",
	        'REVERSE'     => "[7m",

	);

    $out = $_colors["$color"];
    if($out == ""){ $out = "[0m"; }
    if($back){
        return chr(27)."$out$text".chr(27)."[0m";#.chr(27);
    }else{
        echo chr(27)."$out$text".chr(27).chr(27)."[0m";#.chr(27);
    }//fi
}// end function
##############################################


function add_model_to_database($model_name) {

	global $models_added;

	if (in_array($model_name, $models_added))
		return;

	$table_name = Inflection::pluralize($model_name);

	$db = db::getInstance();

	$model_to_be_added = new $model_name;

	$table_correct_type = false;

	$columns_altered = 0;

	try {
		echoc("$table_name\n", 'MAGENTA');
		$q = $db->prepare("DESCRIBE $table_name");
		$q->execute();
		$old_model_fields = $q->fetchAll(PDO::FETCH_COLUMN);

		$p = $db->prepare("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = '".DB_NAME."' AND TABLE_NAME = '$table_name'");
		$p->execute();
		$table_details = $p->fetchAll(PDO::FETCH_COLUMN);

		$r = $db->prepare("select index_name, group_concat(column_name) as columns from information_Schema.STATISTICS where table_schema = '".DB_NAME."' and table_name = '$table_name' and Index_type = 'FULLTEXT' group by index_name;");
		$r->execute();
		$table_index = $r->fetchAll(PDO::FETCH_COLUMN);

		$details_string = "Type: $table_details[0]";
		if (!empty($table_index)) {
			$details_string .= " - Indexed at: ";
			$first_detail_pass = true;
			foreach ($table_index as $column) {
				if ($model_to_be_added->$column->type != 'text') {
					$statement = $db->exec("ALTER TABLE $table_name DROP INDEX $column;");
					echoc("Index removed on $column\n");
				}
				else {
					if (!$first_detail_pass) {
						$details_string .= ", ";
					}
					$details_string .= "$column";
					$first_detail_pass = false;
				}
			}

		}

		echoc("$details_string\n");


	} catch (Exception $e) {
		echoc($e->getMessage(), 'RED');
		echo "\n";
	}

	foreach ($model_to_be_added as $element) {
		if ($element->element_type_identifier == "container") {
			$child_model_name = Inflection::singularize($element->model);
			$child_model = new $child_model_name();
			$parent_id = $element->child_id_name;
			if (!isset($child_model->$parent_id)) {
				echoc("Warning", 'LIGHT_RED');
				echoc(" the column \"$parent_id\" does not exist in the \"$child_model_name\" structure. Please ensure it is added as an id type.\n");
			}
		}
		else {
			if (add_column_if_not_exists($table_name, $element->short_name, $element->database_create_code()))
				$columns_altered++;

			// if we have a text field we may want to perform sull text searching on, ensure table
			// type is MyISAM

			if ($element->type == 'text' && $table_details[0] != "MyISAM" && !$table_correct_type) {
				$statement = $db->exec("ALTER TABLE $table_name ENGINE = MyISAM;");
				echoc("$table_name is now of MyISAM type\n");
				$db->exec("ALTER TABLE $table_name ENGINE = MyISAM;");
				$table_details[0] = "MyISAM";
			}
			if ($element->type == 'text' && $table_details[0] == "MyISAM") {
				$table_correct_type = true;

				// add indexing to column if not already indexed
				if (!in_array($element->short_name, $table_index)) {
					$db->exec("ALTER TABLE $table_name ADD FULLTEXT($element->short_name);");
					echoc("$element->short_name now indexed\n");
				}
			}
		}
		unset($old_model_fields[array_search($element->short_name, $old_model_fields)]);

	}
	$models_added[] = get_class($model_to_be_added);

	if (!$table_correct_type && $table_details[0] != "InnoDB") {
		$statement = $db->exec("ALTER TABLE $table_name ENGINE = InnoDB;");
		echoc("$table_name is now of InnoDB type\n", 'MAGENTA');
		$table_correct_type = true;
	}

	if ($columns_altered < 1) {
		echoc("No columns to update\n", 'LIGHT_BLUE');
	}

	foreach ($old_model_fields as $column_to_delete)
		delete_column($table_name, $column_to_delete);

}

function delete_column($table, $column) {
	if ($column == "id") {
		return;
	}

		$db = db::getInstance();

	    $column_add_query = "ALTER TABLE $table DROP `$column`";
	    try {
			$statement = $db->prepare($column_add_query);
			$statement->execute();
			echoc($column." deleted from ".$table."\n", 'LIGHT_RED');
	    } catch (Exception $e) {
		  	echoc($e->getMessage(), 'RED');
	    }

}

function add_column_if_not_exists($table, $column, $column_attr = "VARCHAR( 255 ) NULL" ){
	if ($column == "id") {
		return;
	}
	$db = db::getInstance();
    $exists = false;
	$query = "SHOW COLUMNS FROM $table";
	$dba = new dbabstraction;
	$statement = $db->prepare($query);
	$statement->execute();
	$columns = $statement->fetchAll(PDO::FETCH_ASSOC);
		    foreach ($columns as $c) {
		        if($c['Field'] == $column){
		            $exists = true;
					// echoc($column." exists\n", 'LIGHT_BLUE');
		            break;
		        }
		    }
		    if(!$exists){
				echoc("Adding ".$column." to ".$table."\n", 'LIGHT_GREEN');
		        $column_add_query = "ALTER TABLE $table ADD `$column` $column_attr";
			    try {
   					$statement = $db->prepare($column_add_query);
   		   			$statement->execute();
			    } catch (Exception $e) {
				  	echoc($e->getMessage(), 'RED');
			    }
			    return true;
		    }
		    else return false;
}

function md5_dir($dir)
{
    if (!is_dir($dir))
    {
        return false;
    }

    $filemd5s = array();
    $d = dir($dir);

    while (false !== ($entry = $d->read()))
    {
        if ($entry != '.' && $entry != '..')
        {
             if (is_dir($dir.'/'.$entry))
             {
                 $filemd5s[] = md5_dir($dir.'/'.$entry);
             }
             else
             {
                 $filemd5s[] = md5_file($dir.'/'.$entry);
             }
         }
    }
    $d->close();
    return md5(implode('', $filemd5s));
}

// detects differences between two folders and returns a list of the different files

function dir_diffs($dir1, $dir2) {

	// dir1 should be old, dir2 new

    if (!is_dir($dir1) || !is_dir($dir2))
    {
        return "Not a folder\n";
    }

    $differences = array();

    $d1 = dir($dir1);
    $d2 = dir($dir2);

    while (false !== ($entry = $d1->read()))
    {
        if ($entry != '.' && $entry != '..' && $entry != ".DS_Store")
        {
        	if (!file_exists($dir2.'/'.$entry)) {
            	$differences[] = $dir1.'/'.$entry.echoc(' no longer exists', "RED", true);
            }
            else if (is_dir($dir1.'/'.$entry)) {
            	$differences = array_merge($differences, dir_diffs($dir1."/".$entry, $dir2."/".$entry));
        	}
            else {
				if (md5_file($dir1.'/'.$entry) != md5_file($dir2.'/'.$entry)) {
                	$differences[] = $dir1.'/'.$entry.echoc(' updated', 'MAGENTA', true);
                }
            }
        }
    }
    $d1->close();

    while (false !== ($entry = $d2->read()))
    {
        if ($entry != '.' && $entry != '..' && $entry != ".DS_Store")
        {
            if (!file_exists($dir1.'/'.$entry)) {
             	$differences[] = $dir1.'/'.$entry." is ".echoc('new', "GREEN", true);
            }
        }
    }
    $d2->close();

    return $differences;

}

class HZip
{
  /**
   * Add files and sub-directories in a folder to zip file.
   * @param string $folder
   * @param ZipArchive $zipFile
   * @param int $exclusiveLength Number of text to be exclusived from the file path.
   */
  private static function folderToZip($folder, &$zipFile, $exclusiveLength) {
    $handle = opendir($folder);
    while (false !== $f = readdir($handle)) {
      if ($f != '.' && $f != '..' && $f != ".DS_Store") {
        $filePath = "$folder/$f";
        // Remove prefix from file path before add to zip.
        $localPath = substr($filePath, $exclusiveLength);
        if (is_file($filePath)) {
          $zipFile->addFile($filePath, $localPath);
        } elseif (is_dir($filePath)) {
          // Add sub-directory.
          $zipFile->addEmptyDir($localPath);
          self::folderToZip($filePath, $zipFile, $exclusiveLength);
        }
      }
    }
    closedir($handle);
  }

  /**
   * Zip a folder (include itself).
   * Usage:
   *   HZip::zipDir('/path/to/sourceDir', '/path/to/out.zip');
   *
   * @param string $sourcePath Path of directory to be zip.
   * @param string $outZipPath Path of output zip file.
   */
  public static function zipDir($sourcePath, $outZipPath = "")
  {
  	echo "Backing up ". $sourcePath."\n";
  	if ($outZipPath == "") {
  		$outZipPath = $sourcePath."/backup-".time().".zip";
  	}
    $pathInfo = pathInfo($sourcePath);
    $parentPath = $pathInfo['dirname'];
    $dirName = $pathInfo['basename'];

    $z = new ZipArchive();
    $z->open($outZipPath, ZIPARCHIVE::CREATE);
    $z->addEmptyDir($dirName);
    self::folderToZip($sourcePath, $z, strlen("$parentPath/"));
    	// close the archive
	if ($z->close()) {
		echo "Archive ". $outZipPath . " created successfully.\n\n";
		return true;
	}
	else {
		echo "Archive could not be created.\n";
		return false;
	}
  }
}

?>
