
$(function () {
   $("[rel=tooltip]").tooltip();
   $(".delete-button").click(function () {
        var btn = $(this)
        btn.button('loading');
	});

   <?php

   if ($app->users) {

   foreach ($app->users as $user): ?>


       $("#delete_button_<?= $user->id ?>").click(function() {
       add_delete_row(<?= $user->id ?>, "<?= address('users', 'delete', $user->id) ?>");
       });

 <?php endforeach;


   } ?>

 });

