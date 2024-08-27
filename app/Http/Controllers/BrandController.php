<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class BrandController extends Controller
{
    public function brands() 
    {
        $brands = Brand::orderBy('id','DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }

    public function add_brand()
    {
        return view('admin.brand-add');
    }

    public function brand_store(Request $request) {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp.'.'.$file_extention;
        $this->GenerateBrandThumbnailsimage($image,$file_name);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('status','Brand has been added successfully');
    }

    public function brand_edit($id) {
        $brand = Brand::find($id);
        return view('admin.brand-edit', compact('brand'));
    }

    public function brand_update(Request $request) {

        

        // $request->validate([
        //     'name' => 'required',
        //     'slug' => 'required|unique:brands,slug,'.$request->id,
        //     'image' => 'mimes:png,jpg,jpeg|max:2048'
        // ]);
        
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,'.$request->id.',id',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        
        if($validator->passes()) {

        $brand = Brand::find($request->id);
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            // $brand->slug = Str::slug($request->name);
            if($request->hasFile('image')) {
                if(File::exists(public_path('uploads/brands').'/'.$brand->image)) {
                    File::delete(public_path('uploads/brands').'/'.$brand->image);
                }
                $image = $request->file('image');
                $file_extention = $request->file('image')->extension();
                $file_name = Carbon::now()->timestamp.'.'.$file_extention;
                $this->GenerateBrandThumbnailsimage($image,$file_name);
                $brand->image = $file_name;
            }
            
            $brand->save();
            return redirect()->route('admin.brands')->with('status','Brand has been updated successfully');
        }
    }

    public function GenerateBrandThumbnailsimage($image, $imageName) {
        $dPath = public_path('uploads/brands');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124, function($constraint){
            $constraint->aspectRatio();
        })->save($dPath.'/'.$imageName);
    }

    public function brand_delete($id) {
        $brand = Brand::find($id);
        if(File::exists(public_path('uploads/brands').'/'.$brand->image)) {
            File::delete(public_path('uploads/brands').'/'.$brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status','Brand has been deleted successfully..!!!');
    }
}
