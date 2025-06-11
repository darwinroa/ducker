jQuery(document).ready(function($) {
  const buttonRedimir = $('#mdw__redimir_cupon-button');

  buttonRedimir.on('click', function() {
    mdw_redimir_cupon_ajax();
  });

  function mdw_redimir_cupon_ajax() {
    const placa = $('#mdw__select_placa').val();
    $.ajax({
      url: wp_ajax.ajax_url,
      type: 'post',
      data: {
        action: 'mdw_redimir_cupon_ajax',
        nonce: wp_ajax.nonce,
        cupon_id: wp_ajax.cupon_id,
        placa
      },
      beforeSend: function(){
        const loaderUrl = wp_ajax.theme_directory_uri + '/inc/img/gear-spinner.svg';
        $('#mdw__redimir_popup').html(`<div class='dc-loader-ajax' bis_skin_checked='1'><img decoding='async' data-src='${loaderUrl}' class=' ls-is-cached lazyloaded' src='${loaderUrl}'></div>`);
      },
      success: function(response) {
        if (response.success) {
            $('#mdw__redimir_popup').html(response.data.message);
        } else {
            $('#mdw__redimir_popup').html('<p>Hubo un error en la solicitud.</p>');
        }
      }
    })
  }
})