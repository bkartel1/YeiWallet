<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\AddressBtc;
use App\TransactionBtc;

class BtcController extends AddressController
{
    protected $network='BTCTEST';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /* Crear nueva wallet BTC
     *
     */
    public function createWallet(Request $request){
        if(!AddressBtc::exists(Auth::user()->id)){
            $newAddress=new AddressBtc;
            $wallet = $this->blockio_new_address($newAddress);
            
            $mensaje='<br><br>¡Felicidades!<strong>'.Auth::user()->usuario.'</strong><br>
                  Tu dirección es<br><strong> '.$wallet->address.' </strong>';
        
            $request->session()->flash('titulo','Address Creada');
            $request->session()->flash('imagen','Imagenes/bitlogo.svg');
            $request->session()->flash('notificacion',$mensaje);
            $request->session()->flash('pie','<h6>Ahora puedes gestionar tus direcciones</h6>');
        }
        
        return redirect('/dashboard');
    }
 
    /* 
     * Enviar Btc desde la address del usuario logeado  
     */
    public function send(){
        $add = AddressBtc::getAddress();
        if(!is_null($add)){
            $balance='0.2';
            $fee='0.0000261';
                
            $balance=$this->blockio_balance($add);
            $fee=$this->blockio_fee($add,$balance);
            
            return view('send_money')
                    ->with('address',$add['address'])
                    ->with('balance',$balance)
                    ->with('comision',$fee)
                    ->with('moneda','Bitcoins')
                    ->with('tipo','btc');
        }
        return redirect('/dashboard');
    }
    
    public function sending(Request $request){
        $add = AddressBtc::getAddress();
        if(!is_null($add)){
            $result=$this->blockio_send($add,$request->address,$request->cantidad);
            
            if($result->status == 'success'){
                $tx = new TransactionBtc;
                $tx->txid = $result->data->txid;
                $tx->tipo = 1;
                $tx->address = $request->address;
                $tx->monto = $result->data->amount_sent;
                $tx->fee = $result->data->network_fee;
                $tx->confirmacion = 0;
                $tx->usuario_id = Auth::user()->id;
                $tx->save();
                
                $mensaje='<br><br>Se ha enviado la cantidad de <strong>' 
                    . $request->cantidad . ' ' . $request->tipo . '</strong><br>
                    A la dirección <br><strong>' . $request->address . '</strong>';
    
                $request->session()->flash('titulo','¡Enviado!');
                $request->session()->flash('imagen','Imagenes/confirmar.svg');
                $request->session()->flash('notificacion',$mensaje);
                $request->session()->flash('pie','<h6>Es posible que la transacción tarde algunos minutos en ser procesada</h6>');
                
                return redirect('/dashboard');
            }
        }
    }
           
    /* 
     * Historial de Btc desde la address del usuario logeado  
     */
    public function history(){
        $add = AddressBtc::getAddress();
        if(!is_null($add)){
            $txs=$this->blockio_get_transactions('received',$add);
            if($txs->status == 'success'){
                TransactionBtc::actTransactionReceived($txs->data->txs,Auth::user()->id,$add['address']);
            }
            
            $txs=$this->blockio_get_transactions('sent',$add);
            if($txs->status == 'success'){
                TransactionBtc::actTransactionSent($txs->data->txs,Auth::user()->id,$add['address']);
            }
            return 'true';
        }
        return redirect('/dashboard');
    }
}
