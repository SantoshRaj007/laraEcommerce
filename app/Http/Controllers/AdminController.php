<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    public function index() 
    {
        return view('admin.index');
    }
    
    public function orders() {
        $orders = Order::orderBy('created_at','DESC')->paginate(12);
        return view('admin.orders',compact('orders'));
    }

    public function order_details($order_id) {
        $order = Order::find($order_id);
        $orderItems = OrderItem::where('order_id',$order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id',$order_id)->first();
        return view('admin.order-details',compact('order','orderItems','transaction'));
    }

    public function update_order_status(Request $request) {
        $order = Order::find($request->order_id);
        $order->status =  $request->order_status;
        if ($request->order_status == 'delivered') {
            $order->delivered_date = Carbon::now();
        } elseif ($request->order_status == 'canceled') {
            $order->canceled_date = Carbon::now();
        }
        $order->save();

        if ($request->order_status == 'delivered') {
            $transaction = Transaction::where('order_id',$request->order_id)->first();
            $transaction->status = 'approved';
            $transaction->save();
        }
        return back()->with("status","Status changed successfully!");
    }
}
