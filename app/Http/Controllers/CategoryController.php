<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class CategoryController extends Controller
{
    // Category Cantroller Function

    public function categories() {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.categories',compact('categories'));
    }

    public function category_add()
    {
        return view('admin.category-add');
    }

    public function category_store(Request $request) {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp.'.'.$file_extention;
        $this->GenerateCategoryThumbnailsimage($image,$file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status','Categories has been added successfully');
    }


    public function GenerateCategoryThumbnailsimage($image, $imageName) {
        $dPath = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124, function($constraint){
            $constraint->aspectRatio();
        })->save($dPath.'/'.$imageName);
    }

    public function category_edit($id) {
        $category = Category::find($id);
        return view('admin.category-edit', compact('category'));
    }


    public function category_update(Request $request) {
        
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'.$request->id.',id',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        
        if($validator->passes()) {

        $category = Category::find($request->id);
            $category->name = $request->name;
            $category->slug = $request->slug;
            // $category->slug = Str::slug($request->name);
            if($request->hasFile('image')) {
                if(File::exists(public_path('uploads/categories').'/'.$category->image)) {
                    File::delete(public_path('uploads/categories').'/'.$category->image);
                }
                $image = $request->file('image');
                $file_extention = $request->file('image')->extension();
                $file_name = Carbon::now()->timestamp.'.'.$file_extention;
                $this->GenerateCategoryThumbnailsimage($image,$file_name);
                $category->image = $file_name;
            }
            
            $category->save();
            return redirect()->route('admin.categories')->with('status','Category has been updated successfully');
        }
    }

    public function category_delete($id) {
        $category = Category::find($id);
        if(File::exists(public_path('uploads/categories').'/'.$category->image)) {
            File::delete(public_path('uploads/categories').'/'.$category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status','Category has been deleted successfully..!!!');
    }
}
