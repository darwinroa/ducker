jQuery(document).ready(function($) {
  let motoAEliminar = null;
  let $botonOriginal = null;

  $(".mdw-eliminar-moto").click(function(e) {
    e.preventDefault();
    e.stopPropagation();

    motoAEliminar = $(this).attr('data-placa');
    $botonOriginal = $(this);

    $("#popup-confirmacion").addClass("show");
  });

  $("#btn-confirmar-eliminar").click(function() {
    if (!motoAEliminar) return;

    $botonOriginal.prop("disabled", true);

    $.ajax({
      url: eliminarmoto.ajaxurl,
      type: 'post',
      data: {
        action: 'eliminar_moto',
        placa: motoAEliminar,
        current_user: eliminarmoto.user_id
      },
      success: function(response) {
        $("#popup-confirmacion").removeClass("show");

        if (response.type === 'success') {
          location.reload();
        } else {
          $botonOriginal.prop("disabled", false);
        }
      },
      error: function() {
        alert("Error al eliminar la moto.");
        $botonOriginal.prop("disabled", false);
        $("#popup-confirmacion").removeClass("show");
      }
    });
  });

  $("#btn-cancelar-eliminar").click(function() {
    $("#popup-confirmacion").removeClass("show");
    motoAEliminar = null;
    $botonOriginal = null;
  });
});