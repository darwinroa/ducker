jQuery(document).ready(function($) {
  const $popup = $('#mdw__redimir_popup');

  // Abrir popup
  $('#mdw__button_redimir').on('click', function() {
    $popup.fadeIn().css('display', 'flex'); // usa flex para centrar
  });

  // Cerrar popup al hacer clic en "Cancelar" o en el overlay
  $('#mdw__cancelar_cupon, .mdw__redimir_popup-overlay').on('click', function() {
    $popup.fadeOut();
  });

  const selectPlaca = $('#mdw__select_placa');
  selectPlaca.on('change', function() {
    $('#mdw__redimir_cupon-button').removeAttr('disabled');
  })
  
  const buttonRedimir = $('#mdw__redimir_cupon-button');
  buttonRedimir.on('click', function() {
    mdw_redimir_cupon_ajax();
  });

  function mdw_redimir_cupon_ajax() {
    const placa = selectPlaca.val();
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
        $('.mdw__redimir_popup-container').html(`<div class='dc-loader-ajax' bis_skin_checked='1'><img decoding='async' data-src='${loaderUrl}' class=' ls-is-cached lazyloaded' src='${loaderUrl}'></div>`);
      },
      success: function(response) {
        if (response.success) {
            $('.mdw__redimir_popup-container').html("<div class='mdw__redimir_popup-response'><span>Cupón redimido correctamente. Podrás ver más información de este cupón en <a href='/mi-cuenta/cupones' class='mdw__message_link'>Mi Cuenta > Cupones</a></span><a href='/mi-cuenta/cupones' class='mdw__button'>Ver mis cupones</a></div>");

        } else {
            $('.mdw__redimir_popup-container').html("<div class='mdw__redimir_popup-response'><span>Hubo un error en la solicitud.</span><a href='/cupones-disponibles' class='mdw__button'>Ver cupones</a></div>");
        }
      }
    })
  }
})