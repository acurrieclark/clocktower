<?php

/**
* baseController
*/
class systemActionController extends baseController
{

	function _container_request() {
		$model = new $this->posted['request'];
		$container = new container(array('short_name' => inflection::pluralize($this->posted['request'])));
		unset($container->values);
		$container->values[$this->posted['key']] = $model;
		header('Content-type: text/html');
		$container->input(false);
	}

	function _files_upload() {

		must_be_admin();

		$this->set_request_type('ajax');


			if ($_REQUEST['directory'] != '' && isset($_REQUEST['directory'])) {
				if (file_exists(dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.$_REQUEST['directory'].'/'))
					$folder = $_REQUEST['directory'].'/';
			}

	  		$upload_handler = new UploadHandler(
			array('script_url' => ABSOLUTE.'files_upload',
	            'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.$folder,
	            'upload_url' => ABSOLUTE.'files/images/'.$folder,
	            'accept_file_types' => '/.+$/i',
	            'orient_image' => true,
	            'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
	            'image_versions' => array(
	            	'large' => array(
	            		'max_width' => 1000,
	            		'max_height' => 590
	            	),
					'modal-thumbnail' => array(
						'max_width' => 470,
						'max_height' => 370
					),
					'large-thumbnail' => array(
						'max_width' => 414,
						'max_height' => 310
					),
					'mid-thumbnail' => array(
						'max_width' => 190,
						'max_height' => 142
					),
	                'thumbnail' => array(
	                    'max_width' => 116,
	                    'max_height' => 87
	                	)
					)
				), false
			);

	        switch ($_SERVER['REQUEST_METHOD']) {
	            case 'OPTIONS':
	            case 'HEAD':
	                $upload_handler->head();
	                break;
	            case 'GET':
					ob_start(array($this, 'add_additional_data'));
		  		        $upload_handler->get();
					ob_end_flush();
	                break;
	            case 'PATCH':
	            case 'PUT':
	            case 'POST':
					logger::Files(json_encode($_FILES));
					ob_start(array($this, 'add_additional_data'));
	  		            $upload_handler->post();
					ob_end_flush();
	                break;
	            case 'DELETE':
	                $upload_handler->delete();
					$this->delete_image();
	                break;
	            default:
	                $this->header('HTTP/1.1 405 Method Not Allowed');
	        }

	}

	function _update_image_details() {

		must_be_admin();

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			error('Incorrect request method');
		}
		else {
			$image = image::find(Array('where' => Array('directory' => $this->posted['directory'], 'name' => $this->posted['filename'])));

			if ($image) {

				$image->display_name->value = $this->posted['display_name'];
				$image->description->value = $this->posted['description'];

				if ($image->update()) {
					flash('Image updated');
					echo json_encode(Array('success' => 'OK'));
				}
				else {
					error('There was a problem saving the image.');
				}
			}
			else {
				error('Image not found');
			}
		}
	}

	function _move_images() {

		must_be_admin();

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			error('Incorrect request method');
		}
		else {

			$old_directory = $this->posted['directory'];
			$new_directory = $this->posted['new_directory'];

			foreach($this->posted['filenames'] as $filename) {

				$new_filename = $filename;

				$count = 1;
				while (file_exists(ROOT."/public/files/images/$new_directory/$new_filename")) {
					$new_filename = pathinfo($filename, PATHINFO_FILENAME)."-$count.".pathinfo($filename, PATHINFO_EXTENSION);
					$count++;
				}

				if (file_exists(ROOT."/public/files/images/$old_directory/$filename")) {
					rename(ROOT."/public/files/images/$old_directory/$filename", ROOT."/public/files/images/$new_directory/$new_filename");
				}
				else {
					echo json_encode(Array('error' => "File $filename does not exist"));
					return;
				}

				//get all files in specified directory
				$folders = glob(ROOT."/public/files/images/$old_directory/" . "*", GLOB_ONLYDIR );

				//print each file name
				foreach($folders as $folder)
				{
					$folder_name = substr( $folder, strrpos( $folder, '/' )+1 );

					if (!is_dir(ROOT."/public/files/images/$new_directory/$folder_name")) {
						mkdir(ROOT."/public/files/images/$new_directory/$folder_name");
					}

					if (file_exists("$folder/$filename")) {

						rename("$folder/$filename", ROOT."/public/files/images/$new_directory/$folder_name/$new_filename");
					}
				}

				$image = image::find(Array('where' => Array('directory' => $this->posted['directory'], 'name' => $filename)));

				$image->directory->value = $new_directory;
				$image->name->value = $new_filename;
				$image->update();

			}
			if (true)
				echo json_encode(Array('status' => 'OK'));
		}
	}

	function _create_image_folder() {

		must_be_admin();

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			error('Incorrect request method');
			redirect_to();
		}
		if ($_POST['new_directory'] != '' && isset($_POST['new_directory']) && !preg_match('/[^*a-zA-Z_\-0-9 ]/', $this->posted['new_directory'])) {

			$folder = $this->posted['new_directory'];
			if (substr($folder, 0, 8) == 'gallery_') {
				$type = 'Gallery';
			}
			else $type = 'Folder';
			if (!is_dir(dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.$folder)) {
			    mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.trim($folder));
				if ($type == 'Gallery') {
					$folder = substr($folder, 8);
					$gallery = new gallery(array('Title' => trim($folder), 'ShortDescription' => $this->posted['description'], 'Published' => $this->posted['published']));
					$gallery->save();
				}
				flash("$type \"$folder\" created");
			}
			else {
				if ($type == 'Gallery') $folder = substr($folder, 8);
				error("$type \"$folder\" already exists");
			}
		}
		else error('Invalid filename provided');
	}

	function _edit_image_folder() {

		must_be_admin();

		if ($_SERVER['REQUEST_METHOD'] != 'GET') {
			error('Incorrect request method');
			redirect_to();
		}

		if ($_GET['directory_name'] != '' && isset($_GET['directory_name']) && !preg_match('/[^*a-zA-Z_\-0-9 ]/', $this->get['directory_name'])) {

			$folder = $this->get['directory_name'];
			if (substr($folder, 0, 8) == 'gallery_') {
				$type = 'Gallery';
				$gallery = gallery::find(array('where' => array('title' => substr($folder, 8))));
				if ($gallery) {
					header('Content-type: text/html');
					$include_security = false;
					echo($gallery->main_form($include_security));
				}
				else error('Could not find gallery');
			}
			else redirect_to();
		}
		else error('Invalid directory provided');
	}

	function _update_image_folder() {

		must_be_admin();

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			error('Incorrect request method');
			redirect_to();
		}
		if ($_POST['title'] != '' && isset($_POST['title']) && !preg_match('/[^*a-zA-Z_\-0-9 ]/', $this->posted['title'])) {

			$folder = trim($this->posted['title']);
			$old_folder = $this->posted['old_folder'];

			if (substr($folder, 0, 8) == 'gallery_') {
				$type = 'Gallery';
			}
			else $type = 'Folder';

			if (!is_dir(dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.$folder) || $old_folder == $folder) {

				if ($old_folder != $folder) {

				    rename(dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.trim($old_folder), dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.trim($folder));

				    if ($images = image::find_all(array('where' => array('directory' => $old_folder)))) {
					    foreach ($images as $key => $image) {
					    	$image->directory->value = $folder;
					    	$image->update();
					    }
				    }				 
				}

				if ($type == 'Gallery') {
					$folder = substr($folder, 8);
					$old_folder = substr($old_folder, 8);
					$gallery = gallery::find(array('where' => array('Title' => $old_folder)));
					$gallery->Title->value = $folder;
					$gallery->ShortDescription->value = $this->posted['description'];
					$gallery->Published->value = $this->posted['published'];
					$gallery->update();
				}
				flash("$type \"$folder\" updated");
			}
			else {
				if ($type == 'Gallery') $folder = substr($folder, 8);
				error("$type \"$folder\" already exists");
			}
		}
		else error('Invalid filename provided');
	}

	function _create_image_version() {

		must_be_admin();

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			error('Incorrect request method');
			redirect_to();
		}
		else {
			if ($_POST['directory'] != '' && isset($_POST['directory'])) {
				if (file_exists(dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.$_POST['directory'].'/'))
					$folder = $_POST['directory'].'/';
			}

			if (!($this->posted['type'] == 'banner' || $this->posted['type'] == 'square' || $this->posted['type'] == 'portrait')) {
				error('Incorrect thumbnail type defined.');
				return;
			}
			else ($thumbnail_type = $this->posted['type']);


			$filename = $this->posted['filename'];
			$upload_dir = dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.$folder;

			if (!is_dir(dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.$folder.$thumbnail_type)) {
			    mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.$folder.$thumbnail_type);
			}

			$save_target = dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.$folder.$thumbnail_type.'/'.$filename;

			$file_type = exif_imagetype($upload_dir.'/'.$filename);

			$image = imageCreateFromFile($upload_dir.'/'.$filename);

			switch ($thumbnail_type) {
				case 'banner':
					$thumb_width = BANNER_IMAGE_WIDTH;
					$thumb_height = BANNER_IMAGE_HEIGHT;
					break;
				case 'square':
					$thumb_width = SQUARE_IMAGE_WIDTH;
					$thumb_height = SQUARE_IMAGE_HEIGHT;
					break;
				case 'portrait':
					$thumb_width = PORTRAIT_IMAGE_WIDTH;
					$thumb_height = PORTRAIT_IMAGE_HEIGHT;
					break;
			}

			$width = imagesx($image);
			$height = imagesy($image);

			$thumb = imagecreatetruecolor( $thumb_width, $thumb_height );
			imagealphablending($thumb, false);
			imagesavealpha($thumb, true);

			// Resize and crop
			imagecopyresampled($thumb,
			                   $image,
			                   0,
			                   0,
			                   $_POST['x1'], $_POST['y1'],
			                   $thumb_width, $thumb_height,
			                   ($_POST['x2'] - $_POST['x1']), ($_POST['y2'] - $_POST['y1']));

			// Output the image

			saveImage($thumb, $save_target, $file_type);

			// Free up memory
			imagedestroy($image);
			imagedestroy($thumb);

			$image = image::find(Array('where' => Array('directory' => $this->posted['directory'], 'name' => $this->posted['filename'])));

			$x1 = $thumbnail_type."_x1";
			$y1 = $thumbnail_type."_y1";
			$x2 = $thumbnail_type."_x2";
			$y2 = $thumbnail_type."_y2";

			$image->$thumbnail_type->value = 'Yes';
			$image->$x1->value = $this->posted['x1'];
			$image->$x2->value = $this->posted['x2'];
			$image->$y1->value = $this->posted['y1'];
			$image->$y2->value = $this->posted['y2'];

			if ($image->update()) {
				flash('Image updated');
				echo json_encode(Array('status' => 'OK'));
			}
			else {
				error('There was a problem saving the thumbnail.');
			}


		}
	}

	function _destroy_container() {
		must_be_admin();
		if ($_SERVER['REQUEST_METHOD'] != 'DELETE') {
			error('Incorrect request method');
			redirect_to();
		}
		if ($this->delete['name'] && $this->delete['type']) {

			if (is_dir(dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.$this->delete['name'])) {

				if ($this->delete['type'] == 'gallery') {
					$folder = substr($this->delete['name'], 8);
					$gallery = gallery::find(array('where' => array('Title' => $folder)));
					if ($gallery) {
						$gallery->destroy();
						delete_directory(dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.$this->delete['name']);
						logger::write();
					}
					else {error('Gallery could not be found'); return;}
				}
				else {
					$folder = $this->delete['name'];
					delete_directory(dirname($_SERVER['SCRIPT_FILENAME']).'/files/images/'.$this->delete['name']);
				}
				flash($this->delete['type']." \"$folder\" deleted");
			}
			else {
				if ($this->delete['type'] == 'gallery') $folder = substr($folder, 8);
				error("$type \"$folder\" does not exist");
			}
		}
		else error('Invalid Details provided');
	}

	function _get_url_title()
		{

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			error('Incorrect request method');
			redirect_to();
		}

		$url = $this->posted['url'];

	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

	    $data = curl_exec($ch);
	    curl_close($ch);

	    $html = $data;

	    //parsing begins here:
		$doc = new DOMDocument();
		@$doc->loadHTML($html);
		$nodes = $doc->getElementsByTagName('title');

		//get and display what you need:
		$title = $nodes->item(0)->nodeValue;

		// reenable below to retieve other metadata

		// $metas = $doc->getElementsByTagName('meta');

		// for ($i = 0; $i < $metas->length; $i++)
		// {
		//     $meta = $metas->item($i);
		//     if($meta->getAttribute('name') == 'description')
		//         $description = $meta->getAttribute('content');
		//     if($meta->getAttribute('name') == 'keywords')
		//         $keywords = $meta->getAttribute('content');
		// }


		echo json_encode(array("title" => $title));

}

	private function add_additional_data($data){

		$array = json_decode($data);
		$data_array = $array->files;

		if ($_REQUEST['directory'] != '' && isset($_REQUEST['directory']))
			$directory_name = $_REQUEST['directory'];
		else $directory_name = "";

		if ($_SERVER['REQUEST_METHOD'] == "POST") {

			foreach ($data_array as $image_data) {
				list($image_data->original_width, $image_data->original_height) = 					getimagesize(ROOT.'/public/files/images/'.$directory_name.DS.$image_data->name);

				$image_data->display_name = preg_replace("/\.[^.\s]{3,4}$/", "", $image_data->name);
				$image_data->description = "";
				$image_data->banner = 'No';
				$image_data->portrait = 'No';
				$image_data->square = 'No';
				$image_data->directory = $directory_name;

				$image_safe_name = url_friendly(pathinfo($image_data->name, PATHINFO_FILENAME)).".".pathinfo($image_data->name, PATHINFO_EXTENSION);

				$path = ROOT.'/public/files/images/'.$directory_name;
				$results = scandir($path);

				$count = 1;

				while (file_exists($path.DS.$image_safe_name)) {
					$image_safe_name = pathinfo($image_safe_name, PATHINFO_FILENAME)."-$count.".pathinfo($image_safe_name, PATHINFO_EXTENSION);
					$count++;
				}

				rename($path.DS.$image_data->name, $path.DS.$image_safe_name);

				foreach ($results as $result) {
				    if ($result === '.' or $result === '..') continue;

				    if (is_dir($path . '/' . $result)) {
						if (file_exists($path.DS.$result.DS.$image_data->name)) {
							rename($path.DS.$result.DS.$image_data->name, $path.DS.$result.DS.$image_safe_name);
						}
				    }
				}

				$unsafe_name = $image_data->name;

				foreach ($image_data as $key => $datum) {
					$image_data->$key = str_replace($unsafe_name, $image_safe_name, urldecode($image_data->$key));
				}


				if ($_REQUEST['directory'] != '' && isset($_REQUEST['directory'])) {
					$image_data->delete_url .= "&directory=".$_REQUEST['directory'].'&security_token='.$_SESSION['SecurityToken'];
				}

				$image = new image(get_object_vars($image_data));
				$image->save();
				$image_data->id = $image->id->value;
			}

		}
		else if ($_SERVER['REQUEST_METHOD'] == "GET") {
			foreach ($data_array as $image_data) {
				$image = image::find(Array('where' => Array('directory' => $directory_name, 'name' => $image_data->name)));

				if ($image) {

					$image_data->id = $image->id->value;

					$image_data->original_width = $image->original_width->value;
					$image_data->original_height = $image->original_height->value;

					$image_data->display_name = $image->display_name->value;
					$image_data->description =  $image->description->value;

					$image_versions = array('banner', 'portrait', 'square');
					$coordinates = array('x1', 'x2', 'y1', 'y2');

					foreach ($image_versions as $version) {
						$image_data->$version = $image->$version->value;
						foreach ($coordinates as $point) {
							$point_name = $version.'_'.$point;
							$image_data->$point_name = $image->$point_name->value;
						}
					}

					$image_data->delete_url .= "&directory=".$directory_name.'&security_token='.$_SESSION['SecurityToken'];
				}
			}
		}

		$array->files = $data_array;
		$data = json_encode($array);
		return $data;
	}

	private function delete_image() {

		if ($_REQUEST['directory'] != '' && isset($_REQUEST['directory']))
			$directory_name = $_REQUEST['directory'];
		else $directory_name = "";

		$image = image::find(Array('where' => Array('directory' => $directory_name, 'name' => $_REQUEST['file'])));
		$image->remove_versions();
		$image->destroy();


	}

}

// WE SHOULD ENSURE NO NON SYSTEM OUTPUT IS RENDERED FROM THIS FILE

?>
