<?php

/**
* Template Class
* Written by Alex
*/
class template
{

	var $level_name, $stylesheets, $javascripts, $contents, $variables;
	var $content_needed = array();
	var $stylesheets_added = array();
	var $javascripts_added = array();
	function __construct()
	{
		ob_start("ob_gzhandler");
		ob_start(array($this, 'parser'));

	}

	function __destruct() {
		ob_end_flush();
	}

	function add_javascript($location) {

		// make sure that we don't add files twice
		if (in_array($location, $this->javascripts_added)) return;
		else $this->javascripts_added[] = $location;

		if (file_exists(ROOT . DS . 'public' .DS. 'js/'.$location.'.js')) {
			if (!isset($this->contents['javascripts'])) $this->contents['javascripts'] = array();
			$this->contents['javascripts'][] = $location;
		}
		else {
			if (!isset($this->contents['javascript_links'])) $this->contents['javascript_links'] = array();
			$this->contents['javascript_links'][] = '<script src="'.$location.'"></script>
			';
		}
	}

	function include_javascript($filename, $variables=array()) {

		global $app;

		ob_start();

		if (file_exists(ROOT . DS . 'application' .DS. 'views' . DS .$app->controller. DS . $filename . '.js.php'))
			include(ROOT . DS . 'application' .DS. 'views' . DS .$app->controller. DS . $filename . '.js.php');
		else if (file_exists(ROOT . DS . 'application' .DS. 'views' . DS .'application'. DS . $filename . '.js.php'))
			include(ROOT . DS . 'application' .DS. 'views' . DS .'application'. DS . $filename . '.js.php');
		else echo "// File $filename not found";
		$this->javascripts .= ob_get_clean(). '
		';
	}

	function javascript_start() {
		ob_start();
	}

	function javascript_end() {
		$this->javascripts .= ob_get_clean();
	}

	function add_stylesheet($location) {
		if (in_array($location, $this->stylesheets_added)) return;
		else $this->stylesheets_added[] = $location;

		if (file_exists(ROOT . DS . 'public' .DS. 'css' . DS . $location . '.css')) {
			if (!isset($this->contents['stylesheets'])) $this->contents['stylesheets'] = array();
			$this->contents['stylesheets'][] = $location;
		}
		else {
			if (!isset($this->contents['stylesheet_links'])) $this->contents['stylesheet_links'] = array();
			$this->contents['stylesheet_links'][] = '<link rel="stylesheet" href="'.$location.'" type="text/css">';
		}
	}

	function content_start($name) {
		$this->level_name[] = $name;
		ob_start();
	}

	function content_end() {
		$this->contents[array_pop($this->level_name)] = ob_get_clean();
	}

	function content_flush($name) {
		$this->contents[$name] = '';
	}

	function content_for($name) {
		echo "{@=".$name."_content=@}";
		$this->content_needed[] = $name;
	}

	function set($name, $value) {
		$this->variables[$name] = $value;
	}

	function parser($buffer) {

		global $app;

		// replace content markers {@=name_content=@} with content from buffer output
		foreach ($this->content_needed as $name) {
			if (!isset($this->contents[$name])) $this->contents[$name] = '';
			$buffer = str_replace("{@=".$name."_content=@}", $this->contents[$name] , $buffer);
		}

		// replace variable markers {@=name} with variables set using set();
		if (isset($this->variables)) {
			foreach ($this->variables as $name => $value) {
				$buffer = str_replace("{@=".$name."}", $value , $buffer);
			}
		}

		// clears any undefined variables if we are not in development mode
		if (DEVELOPMENT_ENVIRONMENT != true)
			$buffer = preg_replace("/\{@=[a-zA-Z0-9 -]*\}/", "", $buffer);

		if (isset($this->contents['stylesheets']))
			$buffer = str_replace("</head>", '<link rel="stylesheet" href="'.ABSOLUTE.'css/css.php?set='.base64_encode(serialize($this->contents['stylesheets'])).'">
		</head>' , $buffer);

		if (isset($this->contents['stylesheet_links']))
			$buffer = str_replace("</head>", join("\n", $this->contents['stylesheet_links']).'
		</head>' , $buffer);


		if (isset($this->contents['javascripts']))
			$buffer = str_replace("</body>", '<script src="'.ABSOLUTE.'js/js.php?set='.base64_encode(serialize($this->contents['javascripts'])).'"></script>
		</body>' , $buffer);

		if (isset($this->contents['javascript_links']))
			$buffer = str_replace("</body>", join("\n", $this->contents['javascript_links']).'
		</body>' , $buffer);

		if (!empty($this->javascripts))
		$buffer = str_replace("</body>",
		'<script type="text/javascript">
		'.$this->javascripts.'
		</script>
		</body>' , $buffer);


		// include google analytics if required
		if (INCLUDE_GOOGLE_ANALYTICS && $_SERVER["SERVER_ADDR"] != "127.0.0.1"):
			$buffer = str_replace("</body>",
				"<script>
		            (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
		            function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
		            e=o.createElement(i);r=o.getElementsByTagName(i)[0];
		            e.src='//www.google-analytics.com/analytics.js';
		            r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
		            ga('create','".GOOGLE_ID_CODE."', '".WEBSITE_ADDRESS."');ga('send','pageview');
		        </script>
	        </body>", $buffer);
		endif;

 		return $buffer;
	}
}


?>
