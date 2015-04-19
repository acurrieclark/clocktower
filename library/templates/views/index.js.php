
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
       add_delete_row(<?= $<%MODEL_NAME%>->id ?>, "<?= address('<%CONTROLLER_LINK_NAME%>', 'delete', $<%MODEL_NAME%>->id) ?>");
       });

 <?php endforeach ?>

      });


// function to add a delete confirmation for below row with id 'row_id' directing delete to 'address'

function add_delete_row(row_id, address) {
 tr_name = 'tr#row_'+row_id;
 cols = $(tr_name).children().length;
 $(tr_name).after('<tr id="delete_row_'+row_id+'"><td colspan="'+cols+'"><div class="pull-right"><span>Are you sure?</span> <a class="btn btn-danger btn-xs" href="'+address+'">Confirm Delete</a> <button class="btn btn-xs" id="delete_cancel_'+row_id+'">Cancel</button></div></td></tr>');
 $('#delete_row_'+row_id)
  .find('td')
  .wrapInner('<div style="display: none;" />')
  .parent()
  .find('td > div')
  .slideDown(200, function(){

   var $set = $(this);
   $set.replaceWith($set.contents());

  });
    $("#delete_cancel_"+row_id).click(function() {
   $('#delete_row_'+row_id)
    .find('td')
    .wrapInner('<div style="display: block;" />')
    .parent()
    .find('td > div')
    .slideUp(200, function(){
     $(this).parent().parent().remove();
    });
   $("#delete_button_"+row_id).button('reset');
    });
}
