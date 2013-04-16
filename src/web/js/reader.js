/**
 * Type can be error, success, block, info
 */
function message(message, type) {
  $('body').prepend('<div id="messages" class="alert alert-' + type + '">\n\
    <button type="button" class="close" data-dismiss="alert">&times;</button>' 
    + message + '</div>');
}

$(document).ready(function() {
  $('#content .entry.unread').waypoint(function(){
    $.get('/post/' + this.id + '/read');
    $('.indicator.icon-eye-open', this)
      .removeClass('icon-eye-open')
      .addClass('icon-eye-close');
  }, { offset: 400 });
  

  $('.remove-subscription').click(function(){
    var btnDelete = $(this);
    var entry = $(btnDelete.parent().parent());
    var id = $(this).attr('data');
    var url = '/subscription/' + id;
    $.ajax({
      url: url,
      type: 'DELETE',
      success: function() {
        entry.remove();
      }
    });
  });

  $('#subscription_new a.btn-primary').click(function(){
    var btnSubmit = $(this);
    var container = $(btnSubmit.parent());
    var input = $('input[name=url]', container);
    var url = input.val();
    
    if (url == '') {
       message('Must be a valid Url', 'error');
       return false;
    }

    //@todo ver si es una url posta

    $.ajax({
      url: '/subscription',
      type: 'POST',
      data: { data: {url: url} },
      success: function(data) {
        var response = $.parseJSON(data);
        if (typeof(response) === 'string') {
          message(data, 'error');
          return false;
        } else {
          //Add entry
          $('#content .alert.alert-error').remove();
          $('#content #subscriptions').prepend('<div id="subscription_' + response.id + '" \n\
            class="subscription thumbnail">\n\
            <div class="">\n\
              <a class="btn btn-danger remove-subscription" data="' + response.id + '">Remove</a>\n\
              <div class="name">' + response.name + '</div>\n\
              <div class="detail">Last Update ' + response.last_update + ' Fails ' + response.fail +  '</div>\n\
            </div></div>');
          console.log(response);
        }
        input.val('');
      },
      error: function(data) {
        console.log(data);
        message(data, 'error');
      }
    }); 


  });
});