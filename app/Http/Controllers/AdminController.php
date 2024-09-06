<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Slide;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at','DESC')->get()->take(10);
        $dashboardDatas = DB::select("Select sum(total) As TotalAmount,
            sum(if(status='ordered',total,0)) As TotalOrderedAmount,
            sum(if(status='delivered',total,0)) As TotalDeliveredAmount,
            sum(if(status='canceled',total,0)) As TotalCanceledAmount,
            Count(*) As Total,
            sum(if(status='ordered',1,0)) As TotalOrdered,
            sum(if(status='delivered',1,0)) As TotalDelivered,
            sum(if(status='canceled',1,0)) As TotalCanceled
            From Orders");
        $monthlyDatas = DB::select("SELECT M.id As MonthNo, M.name As MonthName,
            IFNULL(D.TotalAmount,0) As TotalAmount,   
            IFNULL(D.TotalOrderedAmount,0) As TotalOrderedAmount,   
            IFNULL(D.TotalDeliveredAmount,0) As TotalDeliveredAmount,   
            IFNULL(D.TotalCanceledAmount,0) As TotalCanceledAmount FROM month_names M
            LEFT JOIN (Select DATE_FORMAT(created_at, '%b') As MonthName,
            MONTH(created_at) As MonthNo,
            sum(total) As TotalAmount,
            sum(if(status='ordered',total,0)) As TotalOrderedAmount,
            sum(if(status='delivered',total,0)) As TotalDeliveredAmount,
            sum(if(status='canceled',total,0)) As TotalCanceledAmount
            From Orders WHERE YEAR(created_at)=YEAR(now()) GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b')
            Order By MONTH(created_at)) D On D.MonthNo=M.id");

            $AmountM = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
            $OrderedAmountM = implode(',', collect($monthlyDatas)->pluck('TotalOrderedAmount')->toArray());
            $DeliveredAmountM = implode(',', collect($monthlyDatas)->pluck('TotalDeliveredAmount')->toArray());
            $CanceledAmountM = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());

            $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
            $TotalOrderedAmount = collect($monthlyDatas)->sum('TotalOrderedAmount');
            $TotalDeliveredAmount = collect($monthlyDatas)->sum('TotalDeliveredAmount');
            $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');

        return view('admin.index',compact('orders','dashboardDatas','AmountM','OrderedAmountM','DeliveredAmountM','CanceledAmountM','TotalAmount','TotalOrderedAmount','TotalDeliveredAmount','TotalCanceledAmount'));
    }

    public function orders()
    {
        $orders = Order::orderBy('created_at', 'DESC')->paginate(12);
        return view('admin.orders', compact('orders'));
    }

    public function order_details($order_id)
    {
        $order = Order::find($order_id);
        $orderItems = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id', $order_id)->first();
        return view('admin.order-details', compact('order', 'orderItems', 'transaction'));
    }

    public function update_order_status(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status =  $request->order_status;
        if ($request->order_status == 'delivered') {
            $order->delivered_date = Carbon::now();
        } elseif ($request->order_status == 'canceled') {
            $order->canceled_date = Carbon::now();
        }
        $order->save();

        if ($request->order_status == 'delivered') {
            $transaction = Transaction::where('order_id', $request->order_id)->first();
            $transaction->status = 'approved';
            $transaction->save();
        }
        return back()->with("status", "Status changed successfully!");
    }

    public function slides()
    {
        $slides = Slide::orderBy('id', 'DESC')->paginate(12);
        return view('admin.slides', compact('slides'));
    }

    public function slide_add()
    {
        return view('admin.slide-add');
    }

    public function slide_store(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048'
        ]);

        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->GenerateSlideThumbnailsImage($image, $file_name);
        $slide->image = $file_name;
        $slide->save();
        return redirect()->route('admin.slides')->with("status", "Slide has been added successfully!");
    }

    public function GenerateSlideThumbnailsImage($image, $imageName)
    {
        $dPath = public_path('uploads/slides');
        $img = Image::read($image->path());
        $img->cover(400, 690, "top");
        $img->resize(400, 690, function ($constraint) {
            $constraint->aspectRatio();
        })->save($dPath . '/' . $imageName);
    }

    public function slide_edit($id)
    {
        $slide = Slide::find($id);
        return view('admin.slide-edit', compact('slide'));
    }

    public function slide_update(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048'
        ]);

        $slide = Slide::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/slides').'/'. $slide->image)) {
                File::delete(public_path('uploads/slides').'/'. $slide->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp.'.'.$file_extention;
            $this->GenerateSlideThumbnailsImage($image, $file_name);
            $slide->image = $file_name;
        }
        $slide->save();
        return redirect()->route('admin.slides')->with("status", "Slide has been updaed successfully!");
    }

    public function slide_delete($id) {
        $slide = Slide::find($id);
        if(File::exists(public_path('uploads/slides').'/'.$slide->image)) {
            File::delete(public_path('uploads/slides').'/'.$slide->image);
        }
        $slide->delete();
        return redirect()->route('admin.slides')->with('status','Slide has been deleted successfully..!!!');
    }
}
