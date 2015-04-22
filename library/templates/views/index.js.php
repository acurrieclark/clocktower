
$(function () {
   $("[rel=tooltip]").tooltip();
   $(".delete-button").click(function () {
        var btn = $(this)
        btn.button('loading');
  });

 });

   $(function() {

 <?php foreach ($app-><%CONTROLLER_UNDERSCORE%> as $<%MODEL_NAME%>): ?>


       $("#delete_button_<?= $<%MODEL_NAME%>->id ?>").click(function() {
       add_delete_row(<?= $<%MODEL_NAME%>->id ?>, "<?= address('<%CONTROLLER_UNDERSCORE%>', 'delete', $<%MODEL_NAME%>->id) ?>");
       });

 <?php endforeach ?>

      });

