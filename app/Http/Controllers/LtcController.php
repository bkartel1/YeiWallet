<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\AddressLtc;
use App\TransactionLtc;

class LtcController extends AddressController
{
    protected $network='LTCTEST';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /* Crear nueva wallet LTC
     *
     */
    public function createWallet(Request $request){
        if(!AddressLtc::exists(Auth::user()->id)){
            
            $newAddress=new AddressLtc;
            $wallet = $this->blockio_new_address($newAddress);
            
            $mensaje='<br><br>¡Felicidades! <strong>'.Auth::user()->usuario.'</strong><br>
                  Tu dirección es<br><strong> '.$wallet->address.' </strong>';
        
            $request->session()->flash('titulo','Address Creada');
            $request->session()->flash('imagen','Imagenes/litelogo.svg');
            $request->session()->flash('notificacion',$mensaje);
            $request->session()->flash('pie','<h6>Ahora puedes gestionar tus direcciones</h6>');
        }
        return redirect('/dashboard');
    }
    
    public function send(){
        $add = AddressLtc::getAddress();
        if(!is_null($add)){
            $balance='0.2';
            $fee='0.0000261';
            
            $balance=$this->blockio_balance($add);
            $fee=$this->blockio_fee($add,$balance);
            
            return view('send_money')
                    ->with('address',$add['address'])
                    ->with('balance',$balance)
                    ->with('comision',$fee)
                    ->with('moneda','Litecoins')
                    ->with('tipo','ltc');
        }
        return redirect('/dashboard');
    }
    
    public function sending(Request $request){
        $add = AddressLtc::getAddress();
        if(!is_null($add)){
            $result=$this->blockio_send($add,$request->address,$request->cantidad);
            
            if(strcmp($result->status,'success')==0){
                $tx = new TransactionLtc;
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
     * Historial de Ltc desde la address del usuario logeado  
     */
    public function history(Request $request){
        $add = AddressLtc::getAddress();
        if(!is_null($add)){
            $max=10;
            if($request->session()->get('action', 'null')=='null'){
                $txs=$this->blockio_get_transactions('received',$add);
                if($txs->status == 'success'){
                    TransactionLtc::actTransactionReceived($txs->data->txs,Auth::user()->id,$add['address']);
                }
            
                $txs=$this->blockio_get_transactions('sent',$add);
                if($txs->status == 'success'){
                    TransactionLtc::actTransactionSent($txs->data->txs,Auth::user()->id,$add['address']);
                }
                
                TransactionLtc::ctotal();
                $page=0;
            }else if($request->session()->get('action', 'null') == 'Sig'){
                $page=$request->session()->get('page', 'null')+1;
            }else if($request->session()->get('action', 'null') == 'Ant'){
                $page=$request->session()->get('page', 'null')-1;
            }
            
            $history=TransactionLtc::getTransaction($page*$max,$max,Auth::user()->id);
            $total=TransactionLtc::total();
            $ult=($total/$max)-1;
                
            return view('history')
                ->with('history',$history)
                ->with('tipo','ltc')
                ->with('ult',$ult)
                ->with('total',$total)
                ->with('page',$page);
            
        }
        return redirect('/dashboard');
    }
}
