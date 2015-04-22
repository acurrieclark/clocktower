<?php

set('title', 'Register');
set('header-title', 'Registration');

messages();

?>

<div class="page-header">
	<h1>Register</h1>
</div>
<div class="row">
	<div class="col-md-4 col-md-offset-2">

	<?php

	$this->form->form(ABSOLUTE.'register', NULL);

	?>

	</div>

</div>
