@extends("layaouts.contenido_dashboard")


@section('title')
    Configuración
@endsection

<<<<<<< HEAD
@section('header')
@endsection
=======
 @section('header')
    @section('header_dash')
        @section('menu_nav')
            @include("layaouts.plantilla_navbar")
        @endsection
    endsection
@endsection

>>>>>>> 9c88433bd09a195b3eea63ad6d7e62b19f4fb498

@section("opc4")           
    select
@endsection

@section("v1")           
    show
@endsection

@section("sub2")           
    select2
@endsection

@section('body')
    @section('content')
        <form id="envio_mensae" method="POST" action="{{ route('contact.send') }} " autocomplete="off" onsubmit="return validar_mensaje();">
            {{ csrf_field() }}
            <div id="titulo_trans">Buzon de mensajes</div>

            <div class= "row">
                <div class="div_buzon_msj col-xs-12 col-md-12 col-sm-12 col-md-12">
                    <p>Título</p>
                    <input type="text" id="titulo_msj" class="form-control" onclick="eliminar_error(10);"/>
                    <div id="error_titulo"></div>    
                    <textarea id="redaccion" rows="10" cols="50" class="form-control" name="mensaje" onclick="eliminar_error(11);" style="margin:5px;" form=envio_mensaje></textarea>
                    <div id="error_mensaje"></div>       

                    <input type="submit" class="action-button1" value="Enviar"> 
                </div>  
            </div>
        </form>

        <div id="cambio_datos" class="ocultar">
            <img src="Imagenes/confirmar.svg" width="200">
            <p>Su mensaje se ha enviado, nuestros administradores lo contactaran lo mas pronto posible, gracias por utilizar nuestra pagina.</p>
        </div>
    @endsection
@endsection
