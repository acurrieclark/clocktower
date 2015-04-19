<?php set('title', 'New <%CAPITAL_MODEL_NAME%>') ?>

<div class="page-header">

  <h1><%CAPITAL_CONTROLLER_NAME%> <small>New <%CAPITAL_MODEL_NAME%></small></h1>

  <div class="btn-toolbar">
  	<?php remote_form_buttons('Save', true); ?>
  </div>

</div>


<?=

	messages();

	render_partial('form', array('target' => ABSOLUTE.'<%CONTROLLER_UNDERSCORE%>/new', 'button' => 'Save'	));

