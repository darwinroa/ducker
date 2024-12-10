jQuery(document).ready(function($) {
  $('#query-maintenance').on('click', function() {
    const kilometer = $('#kilometer').val();
    $.ajax({
      url: wp_ajax.ajax_url,
      type: 'post',
      data: {
        action: 'dk_query_maintenance_ajax',
        nonce: wp_ajax.nonce,
        kilometer
      },
      beforeSend: function(){
        const loaderUrl = wp_ajax.theme_directory_uri + '/inc/img/gear-spinner.svg';
        $('#dk_result').html(`<div class='dc-loader-ajax' bis_skin_checked='1'><img decoding='async' data-src='${loaderUrl}' class=' ls-is-cached lazyloaded' src='${loaderUrl}'></div>`);
      },
      success: function(response) {
        if (response.success) {
            $('#dk_result').html(response.data);
        } else {
            $('#dk_result').html('<p>Hubo un error en la solicitud.</p>');
        }
      }
    })
  });
});