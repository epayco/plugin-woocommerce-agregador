��    @                         �   -  �  �  1   A  0   s  /   �  6   �  *     ,   6  7   c    �  	   �     �  6   �  7   	  
   :	     E	  F   [	  =   �	  v   �	     W
     g
     �
  �   �
     4     <     M  '   j  >   �     �     �  :     r   B     �  |   �  �   F     �  (   �  
   �          !  ,   6  e   c  _   �  !   )     K     k  K   �     �     �  .   �  J   #  .   n     �     �     �     �     �     �     	           4     K    \     s  �   �  �     0   �  0     /   A  6   q  *   �  *   �  -   �  	  ,  	   6     @     X  1   p  	   �     �  E   �  ?     u   G     �     �     �  �   �     �     �     �  !   �  *   �            .   .  m   ]     �  l   �  }   J     �  (   �  
   �             $      `   E  Y   �                5  H   S  	   �     �  2   �  D   �  )   ,     V     d     �     �     �     �     �     �     �         Esperando Pago (Onpage Checkout, el usuario al pagar permanece en el sitio) ó (Standard Checkout, el usario al pagar es redireccionado a la pasarela de ePayco) <p><strong>Llaves de comercio inválidas</strong> </p>
                                    <p>Las llaves Public Key, Private Key insertadas<br>
                                        del comercio son inválidas.<br>
                                        Consúltelas en el apartado de integraciones <br>
                                        Llaves API en su Dashboard ePayco.</p> <span class="epayco-required">Descripción</span> <span class="epayco-required">PRIVATE_KEY</span> <span class="epayco-required">PUBLIC_KEY</span> <span class="epayco-required">P_CUST_ID_CLIENTE</span> <span class="epayco-required">P_KEY</span> <span class="epayco-required">Título</span> Acepta tarjetas de credito, depositos y transferencias. Al habilitar esta opción puede exponer información sensible de sus clientes, el uso de esta opción es bajo su responsabilidad, conozca esta información en el siguiente  <a href="https://docs.epayco.co/payments/checkout#scroll-response-p" target="_blank">link.</a> Cancelado Cargando métodos de pago Checkout ePayco (Tarjetas de crédito,debito,efectivo) Checkout ePayco (Tarjetas de crédito,débito,efectivo) Completado Configuración Epayco Corresponde a la descripción que verá el usuario durante el Checkout Corresponde al título que el usuario ve durante el Checkout. Cuando el pago sea Aceptado o Rechazado ePayco envía una confirmación a la tienda para cambiar el estado del pedido. EpaycoAgregador Estado Cancelado del Pedido Estado Final del Pedido Este módulo le permite aceptar pagos seguros por la plataforma de pagos ePayco.Si el cliente decide pagar por ePayco, el estado del pedido cambiará a  Fallido Habilitar ePayco Habilitar el modo de pruebas Habilitar el modo redirección con data Habilitar envió de atributos a través de la URL de respuesta Habilitar/Deshabilitar Habilite para realizar pruebas Habilite para reducir el stock en transacciones pendientes ID de cliente que lo identifica en ePayco. Lo puede encontrar en su panel de clientes en la opción configuración Idioma del Checkout LLave para autenticar y consumir los servicios de ePayco, Proporcionado en su panel de clientes en la opción configuración LLave para firmar la información enviada y recibida de ePayco. Lo puede encontrar en su panel de clientes en la opción configuración Pagar Plugin ePayco Agregador for WooCommerce. Procesando Página de Confirmación Página de Respuesta Reducir el stock en transacciones pendientes Seleccione el estado del pedido que se aplicará a la hora de aceptar y confirmar el pago de la orden Seleccione el estado del pedido que se aplicará cuando la transacciónes Cancelada o Rechazada Seleccione el idioma del checkout Seleccione un tipo de Checkout: Servired/Epayco only support  Si no se cargan automáticamente, haga clic en el botón "Pagar con ePayco" Sitio en pruebas Tipo Checkout Url de la tienda donde ePayco confirma el pago Url de la tienda donde se redirecciona al usuario luego de pagar el pedido Validación de llaves PUBLIC_KEY y PRIVATE_KEY Validar llaves WooCommerce Epayco Agregador agregador Disabled ePayco ePayco Checkout ePayco Pago Cancelado ePayco Pago Completado ePayco Pago Fallido ePayco Procesando Pago http://epayco.co Project-Id-Version: WooCommerce Epayco Agregador 7.0.0
Report-Msgid-Bugs-To: https://wordpress.org/support/plugin/plugin-woocommerce-agregador
Last-Translator: 
Language-Team: English (United States)
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
POT-Creation-Date: 2024-01-29T17:18:49-05:00
PO-Revision-Date: 2024-01-29 22:30+0000
X-Generator: Loco https://localise.biz/
X-Domain: woo-epayco-agregador
Language: en_US
Plural-Forms: nplurals=2; plural=n != 1;
X-Loco-Version: 2.6.6; wp-6.4.2 Waiting for payment (Onpage Checkout, the user remains on the site when paying) or (Standard Checkout, the user when paying is redirected to the ePayco gateway) <p><strong>Invalid trade keys</strong> </p>
<p>Public Key, Private Key keys inserted<br>
of commerce are invalid.<br>
Consult them in the integrations section <br>
API keys in your ePayco Dashboard.</p> <span class="epayco-required">Description</span> <span class="epayco-required">PRIVATE_KEY</span> <span class="epayco-required">PUBLIC_KEY</span> <span class="epayco-required">P_CUST_ID_CLIENTE</span> <span class="epayco-required">P_KEY</span> <span class="epayco-required">Title</span> Accepts credit cards, deposits and transfers. By enabling this option you may expose sensitive information of your clients, the use of this option is under your responsibility, know this information in the following <a href="https://docs.epayco.co/payments/checkout#scroll-response -p" target="_blank">link.</a> Cancelled Loading payment methods Loading payment methods Checkout ePayco (Credit cards, debit cards, cash)  Complete Epayco Configuration Corresponds to the description that the user will see during Checkout It corresponds to the title that the user sees during Checkout. When the payment is Accepted or Rejected, ePayco sends a confirmation to the store to change the status of the order. EpaycoAgregador Canceled Order Status Final Order Status This module allows you to accept secure payments through the ePayco payment platform. If the customer decides to pay by ePayco, the order status will change to Failed Enable ePayco Enable testing mode Enable redirection mode with data Enable sending attributes via response URL Enable/disable Enable for testing Enable to reduce stock in pending transactions Customer ID that identifies you in ePayco. You can find it in your customer panel in the configuration option Checkout Language Key to authenticate and consume ePayco services, Provided in your customer panel in the configuration option Key to sign the information sent and received from ePayco. You can find it in your customer panel in the configuration option Pay Plugin ePayco Agregador for WooCommerce. Processing Confirmation Page Response Page Reduce stock in pending transactions Select the order status that will be applied when accepting and confirming payment for the order Select the order status that will be applied when the transaction is Canceled or Rejected Select the checkout language Select a Checkout type: Servired/Epayco only support  If they are not loaded automatically, click the "Pay with ePayco" button Test site Checkout Type URL of the store where ePayco confirms the payment URL of the store where the user is redirected after paying the order PUBLIC_KEY and PRIVATE_KEY key validation Validate keys WooCommerce Epayco Agregador Disabled aggregator ePayco ePayco Checkout ePayco Payment Canceled ePayco Payment Completed ePayco Payment Failed ePayco Processing Payment http://epayco.co 