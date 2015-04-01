
<?php set('title', 'Show <%CAPITAL_MODEL_NAME%>') ?>


<?php messages(); ?>

<div id="<%MODEL_NAME%>">

<?php

	$this-><%MODEL_NAME%>->show_simple();

?>

</div>
