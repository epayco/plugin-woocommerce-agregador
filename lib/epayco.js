window.onload = function() {
    document.addEventListener("contextmenu", function(e){
        e.preventDefault();
    }, false);
} 
                            
jQuery( document ).ready( function( $ ) {
    $(document).keydown(function (event) {
        if (event.keyCode == 123) {
            return false;
        } else if (event.ctrlKey && event.shiftKey && event.keyCode == 73) {
            return false;
        }
    });
})

var openChekoutAgregador = function () {
    handlerAgregador.open(data)
     console.log("epayco agregador")
  }
  setTimeout(openChekoutAgregador, 2000)  
  var bntPagar = document.getElementById("btn_epayco_agregador");
  bntPagar.addEventListener("click", openChekoutAgregador);