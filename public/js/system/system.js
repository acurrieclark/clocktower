// this is a fix for the jQuery slide effects
function slideToggle(el, bShow){
  var $el = $(el), height = $el.data("originalHeight"), visible = $el.is(":visible");

  // if the bShow isn't present, get the current visibility and reverse it
  if( arguments.length == 1 ) bShow = !visible;

  // if the current visiblilty is the same as the requested state, cancel
  if( bShow == visible ) return false;

  // get the original height
  if( !height ){
    // get original height
    height = $el.show().height();
    // update the height
    $el.data("originalHeight", height);
    // if the element was hidden, hide it again
    if( !visible ) $el.hide().css({height: 0});
  }

  // expand the knowledge (instead of slideDown/Up, use custom animation which applies fix)
  if( bShow ){
    $el.show().animate({height: height}, {duration: 250});
  } else {
    $el.animate({height: 0}, {duration: 250, complete:function (){
        $el.hide();
      }
    });
  }
}

function slideIn(el) {
	slideToggle(el, true);
}

function slideOut(el) {
	slideToggle(el, false);
}

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

// jquery function outerHTML

(function ($) {
  'use strict';

  var getter;

  if ('outerHTML' in $('<div>').get(0)) {
    // native support
    getter = function(){
      return this.get(0).outerHTML;
    };
  } else {
    // no native support
    getter = function(){
      return $('<div>').append(this.first().clone()).html();
    };
  }

  $.fn.outerHTML = function(){
    if (arguments.length)
      return this.replaceWith.apply(this, arguments);
    else
      return getter.call(this);
  };

}(jQuery));

function textCounter(to_be_counted, countfield, maxlimit) {
        var field = $("#"+to_be_counted).find('textarea');
        var counter = $("#"+countfield).find('span');
        var nl_adjustment;
        var newLines = field.val().match(/(\r\n|\n|\r)/g);

        if (newLines !== null) {
          nl_adjustment = newLines.length;
        }
        else nl_adjustment = 0;

        count = maxlimit - field.val().length - nl_adjustment;




        if (count < 0) count = 0;

        if (count === 0) // if too long...trim it!
            {
              if (field.val().match(/(\r\n|\n|\r)$/) !== null) {
                var adjust = 0;
                var count_adjust = 0;
                if (field.val().length + nl_adjustment - maxlimit == 2) adjust = 1;
                if (field.val().length + nl_adjustment - maxlimit !== 0) {
                  field.val(field.val().substring(0, maxlimit - nl_adjustment + adjust));
                  count = maxlimit - field.val().length - nl_adjustment + 1;
                }
              }
              else
                field.val(field.val().substring(0, maxlimit - nl_adjustment));
          }

          counter.text(count);

        if (count < 10) {
          $("#"+countfield).removeClass('label-success');
          $("#"+countfield).removeClass('label-warning');
          $("#"+countfield).addClass('label-danger');
        }
        else if (count < 50) {
          $("#"+countfield).removeClass('label-danger');
          $("#"+countfield).removeClass('label-success');
          $("#"+countfield).addClass('label-warning');
        }
        else {
          $("#"+countfield).addClass('label-success');
          $("#"+countfield).removeClass('label-warning');
          $("#"+countfield).removeClass('label-danger');
        }
      }

jQuery.nl2br = function(varTest){
    return varTest.replace(/(\r\n|\n\r|\r|\n)/g, "<br>");
};
