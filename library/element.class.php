<?php

/**
* Element Class
*/
class element
{

	var $not_required, $unique, $short_name, $input_name, $note, $database_only, $error_message, $error, $style, $many, $default_value, $show, $hide, $display, $confirmation, $variety, $value, $element_type_identifier, $parent_model, $preview_id;

	function __construct($element)
	{
		$this->name = $element[0];
		$this->type = $element[1];
		$this->short_name = (isset($element['short_name'])) ? $element['short_name'] : make_short($this->name);
		$this->input_name = $this->short_name;
		if (isset($element['not_required'])) $this->not_required = $element['not_required'];
		if (isset($element['unique'])) $this->unique = $element['unique'];
		if (isset($element['note'])) $this->note = $element['note'];
		if (isset($element['database_only'])) $this->database_only = $element['database_only'];
		if (isset($element['error'])) $this->error_message = $element['error'];
		if (isset($element['style'])) $this->style = $element['style'];
		if (isset($element['many'])) $this->many = $element['many'];
		if (isset($element['default_value'])) $this->default_value = $element['default_value'];
		if (isset($element['show'])) $this->show = $element['show'];
		if (isset($element['hide'])) $this->hide = $element['hide'];
		if (isset($element['display'])) $this->display = $element['display'];
		if (isset($element['confirmation'])) $this->confirmation = $element['confirmation'];
		if (isset($element['variety'])) $this->variety = $element['variety'];

		if ($this->default_value != "") $this->value = $this->default_value;

	}

	function __tostring() {
		$retVal = ($this->value) ? "$this->value" : "";
		return $retVal;
	}

	function is_empty() {
		if (trim($this->value) == "" || !$this->value) return true;
		else return false;
	}

	function to_database() {
		return "$this";
	}

	function is_required() {
		return !$this->not_required;
	}

	function should_be_shown() {
		if ($this->display === false || $this->database_only || $this->input_only())
			return false;
		else return true;
	}

	function input_only() {
		return false;
	}

	function should_be_unique() {
		return $this->unique;
	}

	function is_unique() {
		$model = $this->parent_model;
		$results = $model::find_all(array('where' => array($this->short_name => $this->value)));

		// discounts the current database entry so that updating works correctly
		if ($this->parent_model_id)
			unset($results[$this->parent_model_id]);

		logger::validation(sizeof($this->parent_model_id));

		if ($results && sizeof($results) > 0) {
			return false;
		}
		else return true;
	}

	function requires_confirmation() {
		if ($this->confirmation) return true;
		else return false;
	}

	function confirmed() {
		if ($this->value == $this->confirmation_value) return true;
		else return false;
	}

	function clear() {
		$this->value = '';
	}

	function validate() {
		if ((trim($this->value) == "" || (!$this->value && $this->value !== '0')) && $this->is_required()) $this->error = not_present;
		else if ($this->should_be_unique()) {
			if (!$this->is_unique())
			 $this->error = not_unique;
		 }
		 else if ($this->requires_confirmation() && !$this->confirmed()) {
			 $this->error = not_confirmed;
		 }
	}

	function show() {
		return show_safely($this);
	}

	function opening_div_class() {
		return "form-group";
	}

	function input_div_class() {
		return "";
	}

	function label() {
		return '<label class="control-label" for="'.$this->short_name.'_'.$this->parent_model.'">'.$this->name.$this->required_marker().'</label>';
	}

	function required_marker() {
		if (!$this->not_required) {
			$returnable = '<span style="display: inline-block; width: 15px; margin-left: 5px;">';
			$returnable .= icon('star');
			$returnable .= "</span>";
			return $returnable;
		}
		else return '';

	}

	function note() {
		if (!empty($this->note)) {
			echo '<p class="note">';
			echo $this->note;
			echo '</p>';
		}
	}

	function process($data) {
		return $data;
	}

	function database_create_code() {
		return "VARCHAR( 255 ) NULL";
	}

	function show_error_message() {
		if ($this->error) {
			echo "<span class=\"help-block\">";
			$this->error_message();
			echo "</span>";
		}
	}

	function error_message() {
		echo $this->error;
	}

	function show_simply() {

		$returnable = '<dt>
						'.$this->name.'
					</dt>
					<dd>
						'.$this->show().'
					</dd>';

		return $returnable;

		}

	function input_hidden() {
		 ?>
		 <input id="<?php echo $this->short_name.'_'.$this->parent_model ?>" type="hidden" name="<?php echo $this->input_name ?>" value="<?php echo show_safely($this->value); ?>" class="id <?php echo $this->short_name;?>">
		 <?php
		}

	}


/**
* String Class
*/
class string extends element
{

	function input() {
		?>
		<input id="<?php echo $this->short_name.'_'.$this->parent_model ?>" type="text" name="<?php echo $this->input_name ?>" value= "<?php echo show_safely($this->value); ?>" class="form-control text <?php echo $this->short_name;?> <?= $this->style ?>" placeholder="<?= $this->name ?>"<?= $this->not_required ? '' : " required" ?>>
		<?php
		$this->show_error_message();
	}

}

class password extends string {

	function input() {
		?>
		<input id="<?php echo $this->short_name.'_'.$this->parent_model ?>" type="password" name="<?php echo $this->input_name ?>" value= "<?php echo show_safely($this->value); ?>" class="form-control password <?php echo $this->short_name;?>"  placeholder="<?= $this->name ?>"<?= $this->not_required ? '' : " required" ?>>
		<?php
		$this->show_error_message();
	}

}

class hashed_password extends string {

	function __construct($element) {
		parent::__construct($element);
		$this->display = false;
	}

	function show() {
		return;
	}

	function input () {
		return;
	}

	function validate() {
		parent::validate();
		if (strlen($this->value) != 60) $this->error = hash_error;
	}

	function database_create_code() {
		return "VARCHAR( 60 ) NOT NULL";
	}

}

class drop extends element {

	function __tostring() {
		foreach ($this->list as $option) {
			if (make_short($this->value) == make_short($option)) {
				return $option;
			}
		}
		return "";
	}

	function __construct($element) {
		parent::__construct($element);
		if (isset($element[2]['model'])) {
			if (!empty($element[2]['where']))
				$where = array('where' => $element[2]['where']);
			else $where = array();

			$items = $element[2]['model']::find_all();

			if (empty($items)) {
				$this->list = array();
			}
			else {
				foreach ($items as $key => $item) {
					$this->list[] = (string)$item->$element[2]['field'];
				}
			}
		}
		else
		$this->list = $element[2];
	}

	function validate() {
		if (($this->value == "nochoice" || $this->value == "") && $this->is_required()) $this->error = not_selected;
		else {
			$found_values = 0;
			foreach ($this->list as $list_name) {
				if (make_short($this->value) == make_short($list_name))
					$found_values++;
			}
			if ($found_values != 1 && $this->value != "nochoice" &&  $this->value != "") $this->error = not_listed;
		}

	}

	function input() {
		?>

		<div class="dropbox">
			<select name="<?php echo $this->input_name ?>" id="<?php echo $this->short_name.'_'.$this->parent_model ?>" class="form-control">
				<option value="nochoice" <?php if ($posted_value == "nochoice") echo 'selected="selected"' ?> >Please choose</option>
				<?php foreach ($this->list as $item_name) {	?>
				<option value="<?php echo make_short($item_name); ?>" <?php if (make_short($this->value) == make_short($item_name) && $this->value != "" && $this->value != "nochoice") echo 'selected="selected"' ?>><?php echo $item_name; ?></option>
				<?php } ?>
				</select>
				<?php $this->show_error_message() ?>
		</div>
				<?php
	}

	function process($data) {
		return make_short($data);
	}

}

class checkbox extends element {

	function __tostring() {
		if (is_array($this->value)) {
			foreach ($this->value as $value) {
				foreach ($this->boxes as $box_name) {
					if (make_short($value) == make_short($box_name))
						$returnable[] = $box_name;
				}
			}
		}
		if (is_array($returnable))
			return implode($returnable, ", ");
		else return "";
	}

	function __construct($element) {
		parent::__construct($element);
		if (isset($element[2]['model'])) {
			if (!empty($element[2]['where']))
				$where = array('where' => $element[2]['where']);
			else $where = array();
			$items = $element[2]['model']::find_all();
			if (empty($items)) {
				$this->boxes = array();
			}
			else {
				foreach ($items as $key => $item) {
					$this->boxes[] = (string)$item->$element[2]['field'];
				}
			}
		}
		else
		$this->boxes = $element[2];
	}

	function label() {
		if ($this->name)
			return '<label>'.$this->name.$this->required_marker().'</label>';
		else return '';
	}

	function process($data) {
		if (is_array($data) || $data == "") {
			return $data;
		}
		else {
			$array = explode("-||-", $data);
			foreach ($array as $key => $option) {
				$returnable[make_short($option)] = make_short($option);
			}
			return $returnable;
		}
	}

	function to_database() {
		if (is_array($this->value)) {
			foreach ($this->value as $value) {
				foreach ($this->boxes as $box_name) {
					if (make_short($value) == make_short($box_name))
						$returnable[] = $box_name;
				}
			}
		}
		if (is_array($returnable))
			return implode($returnable, "-||-");
		else return "";
	}

	function validate() {
		if (empty($this->value) && !$this->not_required)
			$this->error = not_selected;
		if (is_array($this->value)) {
			$total_values = sizeof($this->value);
			$found_values = 0;
			foreach ($this->value as $value) {
				foreach ($this->boxes as $box_name) {
					if (make_short($value) == make_short($box_name))
						$found_values++;
				}
			}
			if ($total_values != $found_values) $this->error = not_listed;
		}
	}

	function opening_div_class() {
		return "";
	}

	function input() {
		?>

		<?php
		foreach ($this->boxes as $item_name) {
			if (make_short($this->value[make_short($item_name)]) == make_short($item_name) && $this->value[make_short($item_name)] != "")
				$checked=' checked="checked"';
			else $checked = "";
			?>
			<div class="checkbox">
			<label>
			<input type="checkbox" name="<?php echo $this->input_name."[".make_short($item_name)."]" ?>"<?php echo $checked; ?> class="<?php echo make_short($item_name); ?>" value="<?php echo make_short($item_name); ?>" id="<?php echo make_short($this->name).make_short($item_name).'_'.$this->parent_model ?>"> <?php echo $item_name ?>
			</label>

		</div>
		<?php
		}
		$this->show_error_message();
		?>

		<?php
	}

}

class number extends string {

	function __construct($element) {
		parent::__construct($element);
	}

	function validate() {

		if (($this->value == "") && $this->is_required()) $this->error = not_present;
		else if (!is_valid_number($this->value) && ($this->value != '')) $this->error = not_number;
		else if ($this->should_be_unique()) {
			if (!$this->is_unique())
			 $this->error = not_unique;
		 }
	}

	function to_database() {
		if ($this->value == "") return NULL;
		else return "$this";
	}

	function database_create_code() {
		return "INT NULL";
	}

}

class float extends number {

	function validate() {

		if (($this->value == "") && $this->is_required()) $this->error = not_present;
		else if (!is_valid_number($this->value) && ($this->value != '')) $this->error = not_number;
		else if ($this->should_be_unique()) {
			if (!$this->is_unique())
			 $this->error = not_unique;
		 }
	}

	function database_create_code() {
		return "FLOAT NULL";
	}
}

class phone_number extends string {

	function __construct($element) {
		parent::__construct($element);
	}

	function validate() {
		if (($this->value == '' && $this->is_required()) || $this->value)
			is_valid_phone_number($this->value, $phone_number_error_no, $this->error);
		parent::validate();
	}

}

/**
*
*/
class social_username extends string
{

	function __construct($element)
	{
		parent::__construct($element);
	}

	function show_simply() {
		if ($this->variety == "twitter") {
			return '<div class="social_link">
				<a href="http://twitter.com/'.$this->value.'" class="zocial twitter" style="margin-top: -5px">'.$this->value.'</a>
			</div>';
		}
		if ($this->variety == "facebook") {
			return '<div class="social_link">
				<a href="http://facebook.com/'.$this->value.'" class="zocial facebook" style="margin-top: -5px">Facebook Profile</a></div>';
		}
		if ($this->variety == "linkedin") {
			return '<div class="social_link">
				<a href="'.$this->value.'" class="zocial linkedin" style="margin-top: -5px">Linked In Profile</a>
			</div>';
		}
	}

	function label() {

		if ($this->variety == "twitter") {
			return '<label for="'.$this->short_name.'_'.$this->parent_model.'"><a href="https://twitter.com/" class="zocial twitter" style="margin-top: -5px">Twitter</a>'.$this->required_marker().'</label>';
		}

		else if ($this->variety == "linkedin") {
			return '<label for="'.$this->short_name.'_'.$this->parent_model.'><a href="http://www.linkedin.com/" class="zocial linkedin" style="margin-top: -5px">Linked In</a>'.$this->required_marker().'</label>';
		}

		if ($this->variety == "facebook") {
			return '<label for="'.$this->short_name.'_'.$this->parent_model.'><a href="http://www.facebook.com/" class="zocial facebook" style="margin-top: -5px">Facebook</a>'.$this->required_marker().'</label>';
		}

		else return '<label for="'.$this->short_name.'_'.$this->parent_model.'><div class="'.$this->type.' icon"></div>'.$this->name.$this->required_marker().'</label>';
	}

	function input() {
		if ($this->variety == 'twitter') {
			 return '<div class="input-prepend">
              <span class="add-on">@</span>

		<input id="'.$this->short_name.'_'.$this->parent_model .'" type="text" name="'. $this->input_name .'" size="25" value= "'. show_safely($this->value) .'" class="text '. $this->short_name.$this->style .'" placeholder="username" />

		'.$this->show_error_message().'

            </div>';
		}
		else if ($this->variety == 'linkedin') {
			 return '<input id="'.$this->short_name.'_'.$this->parent_model .'" type="text" name="'. $this->input_name .'" size="25" value= "'. show_safely($this->value) .'" class="text '. $this->short_name.$this->style .'" placeholder="profile URL" />

		'.$this->show_error_message();
		}
		else if ($this->variety == 'facebook') {
			 return '<input id="'.$this->short_name.'_'.$this->parent_model .'" type="text" name="'. $this->input_name .'" size="25" value= "'. show_safely($this->value) .'" class="text '. $this->short_name.$this->style .'" placeholder="username or profile URL" />

		'.$this->show_error_message();
		}
	else return parent::input();
	}

}


class id extends number {

	function __construct($element) {
		if (!is_array($element)) {
			$new_element[0] = $element;
			$new_element[1] = 'id';
			$element = $new_element;
		}
		parent::__construct($element);
	}

	function is_empty() {
		if ($this->value > 0) return false;
		else return true;
	}

	function input_only() {
		return true;
	}

	function input() {
		?>
			<input id="<?php echo $this->short_name.'_'.$this->parent_model ?>" type="hidden" name="<?php echo $this->input_name ?>" value="<?php echo show_safely($this->value); ?>" class="id <?php echo $this->short_name;?>">
		<?php

	}

	function validate() {

	}

	function database_create_code() {
		return "INT NOT NULL";
	}

}


class email extends string {

	function __construct($element) {
		parent::__construct($element);
	}

	function validate() {
		parent::validate();
		if (!is_valid_email($this->value) && !$this->error && !$this->not_required) $this->error = invalid_email;
	}

	function input() {
		?>
		<input id="<?php echo $this->short_name.'_'.$this->parent_model ?>" type="email" name="<?php echo $this->input_name ?>" value= "<?php echo show_safely($this->value); ?>" class="form-control email <?php echo $this->short_name;?> <?= $this->style ?>" placeholder="<?= $this->name ?>"<?= $this->not_required ? '' : " required" ?>>
		<?php
		$this->show_error_message();
	}

}

/**
 	Radio Buttons Element
	Radio Buttons take options in the form:  short_name => name
**/
class radio extends element
{

	function __tostring() {
		foreach ($this->options as $option) {
			if (make_short($this->value) == make_short($option)) {
				return $option;
			}
		}
		return "";
	}

	function __construct($element)
	{
		parent::__construct($element);
		if (isset($element[2]))
			$this->options = $element[2];
	}

	function opening_div_class() {
		return "form-group";
	}

	function label() {
		return '<label>'.$this->name.$this->required_marker().'</label>';
	}

	function input() {
		?>
				<?php foreach ($this->options as $option_name) {
					$label_class = "radio";
					if ($this->style == "inline") $label_class = "radio-inline";
					?>
					<div class="<?= $label_class ?>">
						<label>
							<input type="radio" id="<?php echo $this->input_name."_".make_short($option_name).'_'.$this->parent_model; ?>" name="<?php echo $this->input_name; ?>" value="<?php echo make_short($option_name) ?>" <?php if (make_short($this->value) == make_short($option_name) && $this->value != "") echo 'checked="checked"' ?> /> <?php echo $option_name; ?>
						</label>
					</div>
				<?php
				}
				$this->show_error_message();
	}

	function process($data) {
		return make_short($data);
	}

	function validate() {
		parent::validate();
		if ($this->not_required && $this->value == "") {
			$this->error = "";
			return true;
		}
		if ($this->error == not_present)
			$this->error = not_selected;
		else {
			$found_values = 0;
			foreach ($this->options as $option_name) {
				if (make_short($this->value) == make_short($option_name))
					$found_values++;
			}
			if ($found_values != 1) $this->error = not_listed;
		}
	}


}


class yesno extends radio {

	function __construct($element) {
		parent::__construct($element);
		$this->options = array("Yes", "No");
		$this->style = 'inline';
	}
}

/**
* text
*/
class text extends element
{

	function __construct($element) {
		if (isset($element['images']))
			$this->images = $element['images'];
		else $this->images = false;
		parent::__construct($element);
	}

	function validate() {
		parent::validate();
		if ($this->style == 'wysiwyg' && (trim(strip_tags($this->value, '<iframe>')) == '') && $this->is_required()) $this->error = not_present;
	}

	function input() {

		?>
	<textarea rows="10" id="<?php echo $this->short_name.'_'.$this->parent_model ?>" name="<?php echo $this->input_name; ?>" class="input-block-level form-control"<?php if ($this->style): ?> style="width: 100%"<?php endif ?><?= $this->not_required ? '' : " required" ?>><?php echo show_safely($this->value) ?></textarea>
		<?php

		$this->show_error_message();

		if ($this->style == 'wysiwyg') {

			javascript_start();
		?>
			$('#<?= $this->short_name.'_'.$this->parent_model ?>').redactor({
	            plugins: ['image_manager'],
				minHeight: 300,
				autoresize: false,
				convertVideoLinks: true,
				observeLinks: true,
				buttons: 	['html', '|', 'formatting', '|', 'undobutton', 'redobutton', '|', 'bold', 'italic', 'deleted', '|',
							'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
							<?= ($this->images) ? '\'image\', ' : '' ?>'table', 'link', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'horizontalrule'],

                buttonsCustom: {
                        undobutton: {
                            title: 'Undo',
                            callback: function(buttonName, buttonDOM, buttonObject) {
                            	this.execCommand('undo');
                            }
                        },
                        redobutton: {
                            title: 'Redo',
                            callback: function(buttonName, buttonDOM, buttonObject) {
                            	this.execCommand('redo');
                            }
                        }
            	}
	        });

		<?php
		javascript_end();
	}


	}

	function process($data) {
		if ($this->images && $data) {

			$data = wrapHTML($data);

			$doc = new DOMDocument();
			$doc->encoding = 'utf-8';
			$doc->loadHTML($data);
			$imgs = $doc->getElementsByTagName('img');

			if (!empty($imgs)) {

				foreach ($imgs as $key => $img) {

					$image = image::find_by_id($img->getAttribute('data-image-id'));

					if ($image)
						$img->setAttribute('src', $image->url($img->getAttribute('data-version')));
					else logger::Images('Image missing');
				}
				return preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body|meta))[^>]*>\s*~i', '', $doc->saveHTML());

			}

		}
		else return $data;
	}

	function to_database() {

		if ($this->style == 'wysiwyg') {

			if ($this->images) {

					if ($this->value) {

						$doc = new DOMDocument();
						$doc->encoding = 'utf-8';
						$doc->loadHTML(wrapHTML($this->value));
						$imgs = $doc->getElementsByTagName('img');

						foreach ($imgs as $img) {

							$text_image['content_id'] = $this->parent_model_id;
							$text_image['type'] = $img->getAttribute('data-version');
							$text_image['image_id'] = $img->getAttribute('data-image-id');
							$text_image['id'] = $img->getAttribute('data-content-image-id');

							$new_text_image = new text_image($text_image);

							if ($text_image['id']) {
								logger::Text_Images('Existing text_image found with id: '.$text_image['id']);
								$new_text_image->update();
							}
							else {
								logger::Text_Images('New text_image found');
								$new_text_image->save();
								$img->setAttribute('data-content-image-id', $new_text_image->id->value);
							}

						}

						$iframes = $doc->getElementsByTagName('iframe');
						foreach ($iframes as $iframe) {
							$iframe->parentNode->setAttribute('class', 'video-container');
						}


						$this->value = preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body|meta))[^>]*>\s*~i', '', $doc->saveHTML());
					}

				}

			require_once (ROOT.'/library/HTMLPurifier/HTMLPurifier.auto.php');

			new HTMLPurifier();
			$config = HTMLPurifier_Config::createDefault();

			$config = HTMLPurifier_Config::createDefault();
			$config->set('Cache.DefinitionImpl', null); // remove this later!
			$config->set('HTML.SafeIframe', true);
			$config->set('URI.SafeIframeRegexp', '%^(http:)*//(www.youtube(?:-nocookie)?.com/embed/|player.vimeo.com/video/)%'); //allow YouTube and Vimeo
			$def = $config->getHTMLDefinition(true);
			$def->addAttribute('img', 'data-image-id', 'Number');
			$def->addAttribute('img', 'data-content-image-id', 'Number');
  			$def->addAttribute('img', 'data-version', new HTMLPurifier_AttrDef_Enum(array('banner','original','square','portrait')));


			$purifier = new HTMLPurifier($config);
			return $purifier->purify($this->value);
		}
		else return "$this->value";
	}

	function database_create_code() {
		return "LONGTEXT NULL";
	}


}


class smallText extends element {

	function __construct($element) {
		parent::__construct($element);
		if (isset($element['limit'])) $this->limit = $element['limit'];
	}

	function input() {
		?>
	<textarea rows="3" id="<?php echo $this->short_name.'_'.$this->parent_model ?>" name="<?php echo $this->input_name; ?>" class="<?= ($this->style) ? $this->style : '' ?> form-control" placeholder="<?= $this->name ?>"<?= $this->not_required ? '' : " required" ?>><?php echo show_safely($this->value) ?></textarea>
		<?php

		$this->show_error_message();

		if ($this->limit) {

			javascript_start();

			?>

			$('#<?php echo $this->short_name.'_'.$this->parent_model ?>').keydown(function(event){
				textCounter('<?php echo $this->short_name.'_'.$this->parent_model."_container" ?>','<?php echo $this->short_name.'_'.$this->parent_model ?>_counter', <?php echo $this->limit ?>);
			});
			$('#<?php echo $this->short_name.'_'.$this->parent_model ?>').keyup(function(event){
				textCounter('<?php echo $this->short_name.'_'.$this->parent_model."_container" ?>','<?php echo $this->short_name.'_'.$this->parent_model ?>_counter', <?php echo $this->limit ?>);
			});

				<?php

				javascript_end();

					if ($this->value != '') $posted_length = $this->limit - strlen($this->value);
					else $posted_length = $this->limit;

					if ($posted_length < 10) $counter_class = 'label-danger';
					else if ($posted_length < 50) $counter_class = 'label-warning';
					else $counter_class = 'label-success';


				 ?>
				 <br />
			<div id="<?php echo $this->short_name.'_'.$this->parent_model; ?>_counter" class="label <?php echo $counter_class ?> counter" style="display: inline-block;">
				<span class="count"><?php echo $posted_length; ?></span> characters left
			</div>

			<?php

		}
	}

	function database_create_code() {
		return "VARCHAR( 3000 ) NULL";
	}

}


/**
* Date
*/
class date extends element
{

	function __tostring() {
		if (!((($this->value[year] == "none")) || ($this->value[month] == "none") || ($this->value[day] == "none") || ($this->value[year] == "") || ($this->value[month] == "") || ($this->value[day] == "") || ($this->value[year] == "00") || ($this->value[month] == "00") || ($this->value[day] == "0000")))
			return $this->value[day]."/".$this->value[month]."/".$this->value[year];
		else return "";
	}

	function __construct($element)
	{
		parent::__construct($element);
		list($this->lower_year_limit, $this->upper_year_limit) = isset($element['range']) ? explode('..', $element['range']) : array(-90, 5);
	}

	function is_empty() {
		if (($this->value['month'] == 'none') && ($this->value['year'] == 'none') && ($this->value['day'] == 'none'))
			return true;
	}

	function day() {
		return $this->value['day'];
	}

	function month() {
		return $this->value['month'];
	}

	function year() {
		return $this->value['year'];
	}

	function to_database() {
		$array = explode('/', $this);
		if ($this->is_empty())
			return;
		else
			return implode('-', array_reverse($array));

	}

	function validate() {
		if (!$this->not_required && $this->is_empty()) $this->error = not_present;
		if (!checkdate((int)$this->value['month'], (int)$this->value['day'] ,(int)$this->value['year']) && !$this->is_empty()) $this->error = invalid_date;
	}

	function input() {

		?>
		<div class="form-inline">
			<div class="date">

		<?php

		$this->day_select();
		$this->month_select();
		$this->year_select();

		?>

			</div>
		</div>
		<?php
		$this->show_error_message();
	}

	function day_select() {
		$days = range (01, 31);
		echo "<select name='".$this->input_name."[day]' class=\"day_select form-control\">";
			echo '<option value="none">Day</option>';
		foreach ($days as $day) {
			if ($day == $this->value['day']) {
				$selected = " selected=\"selected\"";
			}
			else $selected = "";
			if (strlen($day) == 1)
				$day_val = "0".$day;
			else $day_val = $day;
			echo '<option value="'.$day_val.'"'.$selected.'>'.$day.'</option>';
		} echo '</select>';

	}

	function month_select() {
		$months = array('January', 'February', 'March', 'April', 'May', 'June','July', 'August', 'September', 'October', 'November', 'December');
		echo "<select name='".$this->input_name."[month]' class=\"month_select form-control\">";
		echo '<option value="none">Month</option>';
		$month_count = 1;
		foreach ($months as $month) {
			if ($month_count == $this->value['month']) {
				$selected = " selected=\"selected\"";
			}
			else $selected = "";
			if (strlen($month_count) == 1)
				$month_val = "0".$month_count;
			else $month_val = $month_count;
			echo '<option value="'.$month_val.'"'.$selected.'>'.$month.'</option>';
			$month_count++;
		} echo '</select>';

	}

	function year_select() {

		echo "<select name='".$this->input_name."[year]' class=\"year_select form-control\">";
		echo '<option value="none">Year</option>';
			for ($year = (date("Y") + $this->upper_year_limit); $year >= (date("Y") + $this->lower_year_limit); $year--) {
			if ($year == $this->value['year']) {
				echo $year;
				$selected = " selected=\"selected\"";
			}
			else $selected = "";
			echo '<option value="'.$year.'"'.$selected.'>'.$year.'</option>';
		}
		echo '</select>';

	}

	function database_create_code() {
		return "DATE NULL";
	}

	function process($data) {
		if (is_array($data)) {
			return($data);
		}
		else return $this->date_string_to_array($data);

	}

	// takes a date string (- separated) and returns an array
	private function date_string_to_array($string) {
		$exploded_array = explode('-', $string);
		if (sizeof($exploded_array) < 2) {
			$exploded_array = explode('/', $string);
			$exploded_array = array_reverse($exploded_array);
		}
		$date_array['year'] = $exploded_array[0];
		$date_array['month'] = $exploded_array[1];
		$date_array['day'] = $exploded_array[2];
		return $date_array;
	}

}

class timepicker extends string {
	function input() {

		add_javascript('system/moment.min');
		add_javascript('system/bootstrap-datetimepicker.min');
		add_stylesheet('system/bootstrap-datetimepicker.min');

		javascript_start();

		?>

		$('#<?php echo $this->short_name.'_'.$this->parent_model ?>').datetimepicker({
      pickDate: false,
	  useSeconds: false
    });

		<?php

		javascript_end();

		?>
		<div class="time-picker">

			<div id="<?php echo $this->short_name.'_'.$this->parent_model ?>" data-format="HH:mm PP" >
			    <input type="text" name="<?= $this->input_name ?>" value="<?= $this ?>" class="form-control" />
			  </div>
		</div>

		<?php
		$this->show_error_message();
	}
}

class datepicker extends date
{

	function is_empty() {
		if ((!$this->value['month']) && (!$this->value['']) && (!$this->value['day']))
			return true;
	}

	function input() {

		add_javascript('system/moment.min');
		add_javascript('system/bootstrap-datetimepicker.min');
		add_stylesheet('system/bootstrap-datetimepicker.min');

		javascript_start();

		?>

		$('#<?php echo $this->short_name.'_'.$this->parent_model ?>').datetimepicker({
      pickTime: false,
	  maskInput: true
    });

		<?php

		javascript_end();

		?>
		<div class="date-picker">

			<div id="<?php echo $this->short_name.'_'.$this->parent_model ?>" class="date" data-date-format="DD/MM/YYYY">
			    <input type="text" name="<?= $this->input_name ?>" value="<?= $this ?>" class="form-control" />

			  </div>
		</div>

		<?php
		$this->show_error_message();
	}
}

/**
* ApproxDate

*/
class approxDate extends date
{

	function __tostring() {
		if (!((($this->value[year] == "none")) || ($this->value[month] == "none") || (($this->value[year] == "")) || ($this->value[month] == "")))
			return $this->value[month]."/".$this->value[year];
		else return "";
	}

	function __construct($element)
	{
		parent::__construct($element);
	}

	function to_database() {
		$array = explode('/', $this);
		$string = implode('-', array_reverse($array));
		return $string."-01";
	}

	function validate() {
		if (!checkdate((int)$this->value[month], 1 ,(int)$this->value[year])) $this->error = invalid_date;
	}


	function input() {

		?>
		<div class="approxDate">

		<?php

		$this->month_select();
		$this->year_select();

		?>
		</div>
		<?php
		$this->show_error_message();
	}


}


/**
* hidden
*/
class hidden extends element
{

	function __construct($element)
	{
		parent::__construct($element);
	}

	function input_only() {
		return true;
	}

	function input() {
		?>
			<input id="<?php echo $this->short_name.'_'.$this->parent_model ?>" type="hidden" name="<?php echo $this->input_name ?>" value="<?php echo show_safely($this->value); ?>" class="hidden <?php echo $this->short_name;?>" />
		<?php

	}
}
/**
* Container element
*/

class container extends element
{

	function clear() {
		$this->values = Array();
		$this->values[] = new $this->model();
	}

	function is_empty() {
		foreach ($this->values as $model) {
			foreach ($model as $element) {
				if (!$element->is_empty()) return false;
			}
		}
		return true;
	}

	function label() {
		return '<label class="control-label">'.$this->name.'</label>';
	}

	function __tostring() {
		return "Container - Please use foreach element->value";
	}

	function __construct($element)
	{
		parent::__construct($element);
		if (isset($element[2]))
			$this->model = $element[2];
		else {
			$inflect = new Inflection();
			$this->model = $inflect->singularize($this->short_name);
			// create a blank/empty child model for first instance
			$this->values[] = new $this->model();
			$this->total_elements = sizeof($this->values);
		}

		// find the name of the parent model via backtrace
		$trace = debug_backtrace();
	    $previousCall = $trace[2]; // 0 is this call, 1 is call in previous function, 2 is caller of that function
		$this->parent_model = get_class($previousCall['object']);

		$this->child_id_name = $this->parent_model."_id";
		$this->element_type_identifier = 'container';

	}

	function input($add_button = true) {

		global $app;

		if ($this->many && $add_button) {

				echo '<a href="javascript:void(0)" onclick="'.$this->short_name.'_container_request();" class="btn btn-small btn-success" style="float: right;" title="Add"><i class="icon-plus-sign icon-white"></i> Add</a>';


				javascript_start();

				?>

				var <?php echo $this->short_name; ?>_total_elements = <?php echo $this->total_elements+1; ?>;

				function <?php echo $this->short_name ?>_container_request(){
				  $.post("<?php echo ABSOLUTE ?>container_request", {request : '<?php echo $this->model ?>', key : <?php echo $this->short_name; ?>_total_elements, security_token : '<?php echo $app->security_token ?>'}, function(data){
				   if (data.length>0){
					 new_div = $(data);
					 new_div.hide();
					 $("#<?php echo $this->short_name ?>_inputs").append(new_div);
					 new_div_name = '#<?php echo $this->model ?>'+(<?php echo $this->short_name; ?>_total_elements-1);
					 $(new_div_name).slideDown();
				   }
				  })
				<?php echo $this->short_name; ?>_total_elements++;
				}

				<?php

				javascript_end();

			}
			?>



<?php

	$first_entry = true;
		foreach ($this->values as $key => $object) {
			echo '<div id="'.$this->model.$key.'" class="row-fluid">';
			echo '<div class="span10 '.$this->model.'_container">';
			foreach ($object as $element){
				if (!$element->database_only) {
					$element->input_name = $this->short_name."[$key][".$element->short_name."]";
					$element->short_name = $this->model.$key."_".$element->short_name;
					$object->form_entry($element);
				}
			}
			echo '</div>';
			if (!$first_entry || !$add_button)
				echo "<a href=\"javascript:void(0)\" onclick=\"$('#".$this->model.$key."').slideUp(300, function(){\$(this).detach()});\" class=\"btn btn-small btn-danger\" style=\"float: right;\"><i class=\"icon-remove-sign icon-white\" title=\"Remove\"></i></a>";
			echo '</div>';
			$first_entry = false;
		}
	}

	function process($data) {
		if (empty($data))
			$data = array();
		foreach ($data as $key => $datum) {
			$container_model[$key] = new $this->model($datum);
			$container_model[$key]->id->value = $key;
		}
		$this->total_elements = $key;
		return($container_model);
	}

	function validate() {
		if ($this->is_empty() && $this->not_required)
			return;
		foreach ($this->values as $key => $object) {
			$object->validate();
			foreach ($object as $element){
				if ($element->error)
					$this->error = "invalid";
			}
		}
	}

	function show_simply() {
		$returnable = "<h4>$element->name</h4>";
		foreach ($this->values as $model) {
			$returnable .= "<h5>".$this->model." ".$model->id."</h5>";
			$returnable .= $model->show_simple();
		}
		$returnable .= "<br />";
		return $returnable;
	}

}

class _image extends container {

	function __construct($element)
	{

		parent::__construct($element);
		if (!$this->variety) $this->variety = "any";

		if (isset($element['preview_id']))
			$this->preview_id = $element['preview_id'];
		else
			$this->preview_id = "";

		$this->child_id_name = "content_id";

	}

	function label() {
		return;
	}

	function error_message() {
		echo 'Please select an image';
	}

	function input($add_button = false) {

		// loads the image manager module, if it has not already been loaded elsewhere
		include_image_selection();

		foreach ($this->values as $key => $object) {
			foreach ($object as $element){
				if (!$element->database_only) {
					$input_name = $this->short_name."[$key][".$element->short_name."]";
					$short_name = $this->short_name.$key."_".$element->short_name;
					?>

					<input id="<?php echo $short_name ?>" type="hidden" name="<?php echo $input_name ?>" value="<?php echo show_safely($element->value); ?>" class="<?php echo $this->short_name;?> <?= $element->short_name ?>" />

					<?php
				}
			}

			?>
				<div id="<?= $this->short_name.$key.'_image' ?>" class="well">

					<div id="<?= $this->short_name.$key.'_image_holder' ?>" class="content-image-holder">
						<?php

						if ($object->image_id->value) {
							$image = image::find_by_id($object->image_id->value);
							if ($object->type->value != 'original')
								$version_directory = DS.$object->type->value;
							else $version_directory = '';
							$button_text = "Change Image";
							?>
							<img src="<?= ABSOLUTE.'files/images/'.$image->directory.$version_directory.DS.$image->name ?>">
							<?php
						}
						else {
							$text = "Please select an image";
							$button_text = 'Select';
						}
						 ?>
					</div>
					<?php
					if ($this->error) {
						$div_class = ' error';
						$text = '';
					}

					?>
					<div class="control-group<?= $div_class ?>">
						<button type="button" id="<?= $this->short_name ?>_select_button" class="btn btn-primary btn-small image-select-button" data-preview-id="<?= ($this->preview_id) ?>" data-return-id="<?= $this->short_name.$key ?>" data-image-type="<?= $this->variety ?>"><?= $button_text; ?></button> <span id="<?= $this->short_name.$key.'_select_message' ?>" class="help-inline">
							<?php if (!$object->image_id->value) {
								echo $text;
							}
							if ($this->error) $this->error_message();
							?>
						</span>
					</div>
				</div>
			<?php
		}
	}
}

class heading extends element{

	function __construct($element) {
		$this->type = "heading";
		$this->value = $element[0];
	}

	function show() {
		list(, $caller) = debug_backtrace(false);
		if ($caller['function'] == "form_elements")
			return '<legend>'.show_safely($this->value).'</legend>';
		else return '<h3>'.show_safely($this->value).'</h3>';
	}

}

/**
* timestamp
*/
class timestamp extends element
{

	function __construct($element)
	{
		if (!is_array($element)) {
			$new_element[0] = $element;
			$new_element[1] = 'timestamp';
			$new_element['display'] = false;
			$element = $new_element;
		}
		parent::__construct($element);
		$this->database_only = true;
	}

	function database_create_code() {
		return "TIMESTAMP NOT NULL";
	}

	function validate() {

	}

	function input() {}

	function show($format = 'jS F Y \a\t G:i:s') {
		return date($format, strtotime($this->value));
	}

}


/**
* data
*/
class data extends element
{
	function __construct($element) {
		parent::__construct($element);
	}

	function database_create_code() {
		return "LONGTEXT NOT NULL";
	}

}

/**
* id_code
*/
class id_code extends element
{

	function __construct($element)
	{
		parent::__construct($element);
		$this->display = false;
	}

	function validate() {}

	function database_create_code() {
		return "CHAR(32) NOT NULL";
	}


}


?>
