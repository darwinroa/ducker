jQuery(document).ready(function($) {
  $(".mdw-eliminar-moto").click(function(e) {
    e.stopPropagation();
    let $btn = $(this);
    $btn.prop("disabled", true);

    const placa = $btn.attr('data-placa');

    $.ajax({
        url: eliminarmoto.ajaxurl,
        type: 'post',
        data: {
            action: 'eliminar_moto',
            placa: placa,
            current_user: eliminarmoto.user_id
        },
        success: function(response) {
            if (response.type === 'success') {
                alert(response.message);
                location.reload();
            } else {
                alert(response.message);
                $btn.prop("disabled", false);
            }
        },
        error: function() {
            alert("Error al eliminar la moto.");
            $btn.prop("disabled", false);
        }
    });
  });
});