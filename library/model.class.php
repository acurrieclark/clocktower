<?php

/**
* Model
*/
abstract class model
{

	function __construct($data = array())
	{
		$this->id = new id('id');
		$this->updated_at = new timestamp('updated_at');
		$this->created_at = new timestamp('created_at');

		$this->load_structure();

		foreach ($this as $element) {
			if (!empty($data)) {
				if (isset($data[$element->short_name]))
					$processed_data = $element->process($data[$element->short_name]);
				else $processed_data = '';
				if ($element->element_type_identifier == "container") {
					$element->values = ($processed_data) ? $processed_data : $element->values;
				}
				else {
					$element->value = ($processed_data || ($processed_data === '0')) ? $processed_data : $element->value;
				}
			}

			$element->parent_model = get_class($this);
			$element->parent_model_id = $this->id->value;
		}
	}

	function set_id($id) {
		if (!isset($this->id))
			$this->id = new id('id');
		$this->id->value = $id;
		foreach ($this as $element) {
			$element->parent_model_id = $id;
		}
	}

	function save($keep_id = false, $run_validations = true) {
		logger::Model('Saving '.get_class($this));
		if (!$run_validations || $this->validate()) {
			$this->mask_commit();
			$commit_id = $this->commit_save($keep_id);
			if ($commit_id) {
				$containers = $this->containers();
				if (!is_array($containers)) $containers = array();
				foreach ($containers as $container) {
					logger::Model("Saving $container container");
					foreach ($this->$container->values as $model) {
						$child_id_name = $this->$container->child_id_name;
						$model->$child_id_name->value = $commit_id;
						$model->save();
					}
				}
				// set the model id to the returned commit_id
				$this->set_id($commit_id);

				$this->activate_reset_security();
				return true;
			}
			// model could not be saved
			else {
				logger::Model('Could not be saved');
				if (isset($this->errors)) {
					foreach($this->errors as $error) {
						logger::Model("MYSQL Error: ".$error);
					}
					unset($this->errors);
				}
				return false;
			}
		}
		else {
			// model has failed validation
			$this->cancel_reset_security();
			return false;
		}
	}

	function update($run_validations = true, $mask_commit = false) {
		$valid = false;
		logger::Model('Updating '.get_class($this));
		if ($run_validations) $valid = $this->validate();
		if ($valid === true || !$run_validations) {
			if ($mask_commit) $this->mask_commit();
			$rows_updated = $this->commit_update();
			$containers = $this->containers();
			if (!is_array($containers)) $containers = array();

			foreach ($containers as $container) {
				logger::Model("Updating $container container");
				$child_id_name = $this->$container->child_id_name;
				$db_name = $this->$container->model;
				$database_models = $db_name::find_all(array('where' => array($child_id_name => $this->id->value)));


				// if the container is both required by being dependent on its owner and is not marked as
				// not_required, we save or update.

				if (!(!$this->element_is_required($this->$container)
					|| (!$this->$container->is_required() && $this->$container->is_empty()))) {
						foreach ($this->$container->values as $model) {
							$model->$child_id_name->value = $this->id->value;
							logger::container_id($model->id->value);
							if (isset($database_models[$model->id->value])) {
								$model->update();
							}
							else {
								unset($model->id);
								$model->save();
							}
						}
				}

				// regardless of if the container is required, we delete any appropriate database entries

				if (!empty($database_models)) {

					foreach ($database_models as $database_model) {

						$matched = false;

		 				foreach ($this->$container->values as $model) {
							if ($model->id->value == $database_model->id->value) {
								$matched = true;
								continue;
							}
		 				}

						if (!$matched && ($this->id->value == $database_model->$child_id_name->value) && $database_model->id->value > 0) {
							$database_model->destroy();
						}

					}
				}
			}
			$this->activate_reset_security();
			return true;
		}
		else {
			$this->cancel_reset_security();
			return false;
		}
	}

	function mask_commit() {
		foreach ($this as $key => $element) {
			if (!in_array($element->short_name, $this->required_list())) {
				unset($this->$key);
			}
		}

	}

	function destroy() {
		$table_name = Inflection::pluralize(get_class($this));
		$dbAbstraction = new dbAbstraction;
		logger::Model(get_class($this).$this->id." removed\n");
		return $dbAbstraction->delete($table_name, $this->id->value);
	}

	function commit_save($keep_id = false) {
		foreach ($this as $element) {
			if ($element->element_type_identifier != "container") {
				// if the element is not empty, add it to the database list
				if ($element->value != "" && ($element->value || $element->value === '0')) {
					$committable[$element->short_name] = $element->to_database();
				}
			}
		}
		if (!$keep_id) unset($committable['id']);
		$committable['created_at'] = date('Y-m-d H:i:s');
		$table_name = Inflection::pluralize(get_class($this));
		return dbAbstraction::insert($table_name, $committable);
	}

	function commit_update() {
		foreach ($this as $element) {
			if ($element->element_type_identifier != "container") {
					$committable[$element->short_name] = $element->to_database();
			}
		}
		unset($committable['updated_at']);
		unset($committable['created_at']);
		$table_name = Inflection::pluralize(get_class($this));
		$dbAbstraction = new dbAbstraction;
		return $dbAbstraction->update($table_name, $this->id->value, $committable);
	}

	function form($address="", $button_text="Submit",  $style = "normal", $cancel = true) {

		$this->form_header($address, $style);

		$this->main_form();

		if ($button_text == '') $button_text = 'Submit';

		$this->form_button($button_text, $cancel);

		$this->form_footer();


	}

	function form_header($address, $style = "normal") {
		global $app;
		if ($style != 'normal')
			$class = ' class="form-horizontal';
		$class .= ' '.inflection::singularize($app->controller).' '.inflection::singularize($app->controller).'-'.$app->action.'"';
		?>
			<form role="form" action="<?php echo $address; ?>" method="post" accept-charset="utf-8"<?= $class ?>>
		<?php
	}

	function form_footer() {
		?>
			</form>
		<?php
	}

	function main_form($include_security = true)
	{
		if ($include_security) $this->setup_security();

		$this->form_elements();

		if ($include_security) $this->form_security();
	}

	function form_button($text, $cancel = true) {
		?>
			<div class="form-group">
			<input type="submit" value="<?php echo $text ?>" name="submit" class="btn btn-primary" id="form-submit-button"/>
			<?php
			if ($cancel === true) {
				echo link_to("Cancel", "back", "", "", array("class" => "btn btn-default", "id" => 'form-cancel-button'));
			}
			else if ($cancel)
				echo link_to("Cancel", $cancel, "", "", array("class" => "btn btn-default", "id" => 'form-cancel-button'));
			?>
			</div>
		<?php
	}

	function setup_security() {
		// Create Security for form
		global $app;
		if (!isset($app->security_token)) {
			$app->security_token = md5(uniqid(rand(), TRUE));
			$_SESSION['SecurityTokenCount'] = 0;
			$_SESSION['SecurityTokenTime'] = time();
		}
		$_SESSION['SecurityToken'] = $app->security_token;

	}

	function form_security() {
		global $app;
		$security_token = new hidden(array("security_token", 'hidden'));
		$security_token->value = $app->security_token;
		echo $security_token->input();
	}

	function activate_reset_security() {
		global $app;
		if (isset($app) && $app->request_type != "ajax")
			$app->activate_reset_security();
	}

	function cancel_reset_security() {
		global $app;

		if (isset($app) && $app->request_type != "ajax")
			$app->cancel_reset_security();
	}

	function model_table_header($to_be_shown = array(), $show_controls = true) {

		$returnable = '';

		if (!empty($to_be_shown)) {
			foreach ($to_be_shown as $element_name) {
				if ($this->$element_name->should_be_shown()) {

					$returnable .= "<th>$element_name</th>";
				}
			}
		}
		else {
			foreach ($this as $element) {
				if ($element->should_be_shown()) {
					$returnable .= "<th>$element->name</th>";
				}
			}
		}

		return $returnable;

	}

	function model_table_row($to_be_shown = array(), $show_controls = true) {

		$returnable = '';

		if (!empty($to_be_shown)) {
			foreach ($to_be_shown as $element_name) {
				if ($this->$element_name->should_be_shown()) {
					$returnable .= '<td>'.show_safely($this->$element_name).'</td>';
				}
			}
		}
		else {
			foreach ($this as $element) {
				if ($element->should_be_shown()) {
					$returnable .= '<td>'.show_safely($element).'</td>';
				}
			}
		}

		return $returnable;

	}

	function show_simple($to_be_shown = array(), $show_empty = false) {

		$returnable = '<dl class="dl-horizontal">';

		if (!empty($to_be_shown)) {
			foreach ($to_be_shown as $element_name) {
				if ($this->$element_name->should_be_shown() && $this->element_is_required($this->$element_name) &&
					($this->$element_name->value || $show_empty))
					$returnable .= $this->$element_name->show_simply();
			}
		}
		else {
			foreach ($this as $element) {
				if ($element->type == "heading")
					$returnable .=  $element->show();
				else if ($element->should_be_shown() && $this->element_is_required($element) &&
					($element->value || $show_empty)) {
					$returnable .= $element->show_simply();
				}
			}
		}
		$returnable .= "</dl>";
		return $returnable;
	}

	function label($element) {
		return $element->label();
	}

	function note($element) {
		return $element->note();
	}

	function input($element) {
		return $element->input();
	}

	function javascript($element) {
		if ($element->show) {
			$this->javascript_enable_hide($element);
		}
	}

	function javascript_enable_hide($element) {

		$div_id = $element->short_name.'_'.$element->parent_model;
		$show_triggers = $element->show;
		$hide_triggers = $element->hide;


		javascript_start();

				$should_be_hidden = true;
				foreach ($show_triggers as $depends_on => $depends_on_trigger) {
					$show_triggers_array = explode(" || ", $depends_on_trigger);

				foreach ($show_triggers_array as $trigger) {
					if (make_short($this->$depends_on->value) == make_short($trigger)) {
						 $should_be_hidden = false;
					 } ?>

					$('#<?php echo $depends_on."_".make_short($trigger).'_'.$element->parent_model; ?>').click(function(){
						$('#<?php echo $div_id ?>_container').slideDown();
					});

				<?php }
				}
				foreach ($hide_triggers as $hide_depends_on => $hide_depends_on_trigger) {
					$hide_triggers_array = explode(" || ", $hide_depends_on_trigger);

				foreach ($hide_triggers_array as $hide_trigger) {
					if (make_short($this->$hide_depends_on->value) == make_short($hide_trigger)) {
						 $should_be_hidden = true;
					 } ?>

				<?php }
				}

				if ($should_be_hidden) echo "$('#".$div_id."_container').hide();";

				foreach ($hide_triggers as $depends_on => $depends_on_trigger) {
					$triggers = explode(" || ", $depends_on_trigger);

				foreach ($triggers as $trigger) {
		?>
			$('#<?php echo $depends_on."_".make_short($trigger).'_'.$element->parent_model; ?>').click(function(){
				$('#<?php echo $div_id ?>_container').slideUp();
			});


		<?php
		   }
		 	}


			javascript_end();

	}


	function form_entry($element, $show_label = true) {

		if (!is_object($element)) {
			$element = $this->$element;
		}

		$element_control_class = $element->opening_div_class();
		if ($element->error && $element->element_type_identifier != "container") $element_control_class .= ' has-error';
		if (!$element->input_only()) {
			echo "<div id=\"".$element->short_name."_".$element->parent_model."_container\" class=\"$element_control_class\">";
				if ($show_label) {
					echo $element->label();
				}
				echo "<div id=\"".$element->short_name."_inputs\">";
					echo $this->input($element);
					echo $this->note($element);
					echo $this->javascript($element);
				echo "</div>";
			echo "</div>";
		}
		else echo $this->input($element);
	}

	function form_elements() {
			$title_count = 0;
			$first_pass = true;
		foreach ($this as $element) {
				if (get_class($element) == 'heading') {
				if ($title_count > 0)
					echo '</fieldset>';

				echo '<fieldset>';
				echo $element->show();

				$title_count++;
			}
			else {
				if ($title_count < 1 && $first_pass)
					echo '<fieldset>';
			if (!$element->database_only && $element->display !== false) {
				echo $this->form_entry($element);
			}
		}
		$first_pass = false;
	}
		echo "</fieldset>";

	}

	function validate() {
		global $valid_models, $invalid_models;
		if (!is_array($valid_models)) $valid_models = array();
		if (!is_array($invalid_models)) $invalid_models = array();
		// if we have already validated the model, don't do it again
		if (in_array(get_class($this).$this->id, $valid_models)) return true;
		else if (in_array(get_class($this).$this->id, $invalid_models)) return false;

		$required_list = $this->required_list();

		$error_string = "";

		if (in_array("custom_validations", get_class_methods($this))) {
			$this->custom_validations();
		}

		foreach ($this as $key => $element) {
			if (in_array($element->short_name, $required_list)) {
				if ($element->requires_confirmation()) {
					$confirmation_element_name = $element->short_name."_confirmation";
					$element->confirmation_value = $this->$confirmation_element_name->value;
				}
				$element->validate();
				if ($element->error) {
					if ($element->error == not_confirmed && $confirmation_element_name != "") {
						$this->$confirmation_element_name->error = not_confirmed_either;
					}
					$error_string .= get_class($this).$this->id." - ".$element->name." - ".$element->error . "\n";
				}
			}
			else {
				$element->error = '';
				$element->clear();
			}
		}

		if ($error_string != "") {
			logger::Invalid($error_string);
			if ($this->id->value)
				$invalid_models[] = get_class($this).$this->id;
			return false;
		}
		else {
			logger::Valid(get_class($this).$this->id." validated successfully\n");
			if ($this->id->value)
				$valid_models[] = get_class($this).$this->id;
			return true;
		}
	}

	// lists all the elements which need to be validated

	function required_list() {
		$required_list = array();
		foreach ($this as $element) {
			if ($this->element_is_required($element))
				$required_list[] = $element->short_name;
		}
		return $required_list;
	}

	function element_is_required($element) {
		if ($element->show){
			foreach ($element->show as $depends_on => $depends_on_trigger) {
				$triggers = explode(" || ", $depends_on_trigger);
			foreach ($triggers as $trigger) {
				if ((($this->$depends_on->value == make_short($trigger)) && !empty($this->$depends_on->value))) {
					return true;
				}
				}
			}
			return false;
		}
		else return true;
	}

	function structure() {
			$model_name = get_class($this);
			if (file_exists (ROOT . DS . 'application' . DS . 'structures' . DS . $model_name . '.php')) {
				require (ROOT . DS . 'application' . DS . 'structures' . DS . $model_name . '.php');
				return $structure;
		}
	}

	function load_structure() {

		if (model_contains_headings($this->structure()) && get_parent_class($this) != 'formTemplate')
			throw new Exception("The model appears to contain headings", 1);

			foreach ($this->structure() as $element) {
				list($element_name, $element_short_name, $element_type, $element_details) = get_element_details($element);
				if ($element_short_name && isset($this->$element_short_name)) {
					$duplicate_name = $this->$element_short_name->name;
					throw new Exception("The model appears to contain duplicate short_name keys $duplicate_name", 1);
				}
				else{
					// used for forms where we borrow the element details from the model
					if (isset($element['model']) && $element['element']) {
						$element_short_name = $element['element'];
						if (!isset($inter_models[$element['model']])) {
							logger::Form('Loading blank '.$element['model'].' model.');
							$inter_models[$element['model']] = new $element['model']();
						}
						$this->$element_short_name = $inter_models[$element['model']]->$element['element'];
					}
					else $this->$element_short_name = new $element_type($element);
					if ($this->$element_short_name->requires_confirmation()) {
						$confirmation_short_name = $element_short_name."_confirmation";
						$this->$confirmation_short_name = new $element_type(array($element_name.' Confirmation', $element_type, 'short_name' => $confirmation_short_name));

				}
			}

		}

	}

	public static function find_by_id($id, $options = array()) {
		$class = get_called_class();
		return $class::find(array_merge($options, array('where' => array('id' => $id))));
	}

	public static function find($options = array('where' => array(), 'order' => array())) {
		$time_start = microtime(true);
		$table = Inflection::pluralize(get_called_class());
		$dbinstance = new dbabstraction;
		$dbinstance->select($table);
		if (!empty($options['where'])) {
			foreach ($options['where'] as $field => $value) {
				$dbinstance->where($field, $value);
			}
		}
		if (!empty($options['order'])) {
			foreach ($options['order'] as $by => $order) {
				$dbinstance->orderBy($by, $order);
			}
		}
		$dbinstance->limit(0, 1);
		$stmt = $dbinstance->query();
		$stmt->execute();
		$model = Inflection::singularize($table);
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if (empty($result))
			return;
		$model_instance = new $model($result);
		if (!$options['no_containers'])
			$model_instance->fetch_associated_models();
		logger::Database("1 $model with $field=$value fetched.");
		$time_finish = microtime(true);
		$time = $time_finish - $time_start;
		logger::Database('Completed in '.$time.' seconds');
		return $model_instance;
	}

	public static function find_all($options = array()) {

		$time_start = microtime(true);
		if (!isset($options['where']))
			$options['where'] = array();
		if (!isset($options['order']))
			$options['order'] = array();
		if (!isset($options['paginate']))
			$options['paginate'] = true;
		if (!isset($options['page']))
			$options['page'] = 1;
		if (!isset($options['limit']))
			$options['limit'] = PAGINATE_LIMIT;

		$class = get_called_class();

		if (isset($options['tables']))
			$table = $options['tables'];
		else
			$table = Inflection::pluralize($class);
		$dbinstance = new dbabstraction;
		$dbinstance->select($table);
		if (!empty($options['where'])) {
			foreach ($options['where'] as $field => $value) {
				$dbinstance->where($field, $value);
			}
		}
		if (!empty($options['order'])) {
			foreach ($options['order'] as $by => $order) {
				$dbinstance->orderBy($by, $order);
			}

		}

		$row_count = $class::count(array('where' => $options['where']));

		if ($row_count > 0) {

			$start = ($options['page'] - 1) * $options['limit'];

			if ($options['paginate'] && $row_count > $options['limit']) {
				$dbinstance->limit($start, $options['limit']);
			}
			$stmt = $dbinstance->query();
			$stmt->execute();
			$count = 0;
			foreach ($stmt->fetchAll() as $object){
				$objects_array[$object['id']] = new $class($object);
				if (!$options['no_containers'])
					$objects_array[$object['id']]->fetch_associated_models();
				$count++;
			}
			$models_name = Inflection::pluralize_if($count, $class);
			logger::Database("$models_name fetched.");
		}
		$time_finish = microtime(true);
		$time = $time_finish - $time_start;
		logger::Database('Completed in '.$time.' seconds');
		if (isset($objects_array))
			return $objects_array;
		else return;
	}

	// returns the number of rows from a query

	public static function count($options = array('where' => array())) {
		$table = Inflection::pluralize(get_called_class());
		$dbinstance = new dbabstraction;
		$dbinstance->count($table);
		if (!empty($options['where'])) {
			foreach ($options['where'] as $field => $value) {
				$dbinstance->where($field, $value);
			}
		}
		$stmt = $dbinstance->query();
		$stmt->execute();
		$result = $stmt->fetchColumn();
		logger::Database("$result $table found");
		return $result;
	}

	// returns an array of the short_names of any container elements

	public function containers() {
		foreach ($this as $element) {
			if ($element->element_type_identifier == "container"){
				$db_name = $element->short_name;
				$containers[] = $db_name;
			}
		}
		if (isset($containers))
			return $containers;
		else return false;
	}

	public function fetch_associated_models() {
		$containers = $this->containers();
		if (!is_array($containers)) $containers = array();
		foreach ($containers as $container) {
			$model_id_name = $this->$container->child_id_name;

			$model_name = $this->$container->model;

			$this->$container->values = $model_name::find_all(array('where' => array($model_id_name => $this->id)));

			if (empty($this->$container->values)) $this->$container->clear();
			$this->$container->total_elements = max(array_keys($this->$container->values));
		}
	}

	public function values() {
		foreach ($this as $element) {
			if (isset($element->value)) {
				$returnable[$element->short_name] = $element->value;
			}
		}
		return $returnable;
	}

	public function json_values() {
		$object = new stdClass();
		if (!empty($this->values));
		foreach ($this->values() as $key => $value) {
			$object->$key = $value;
		}
		return json_encode($object);
	}

}


?>
