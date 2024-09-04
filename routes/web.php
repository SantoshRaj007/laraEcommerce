<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');

Route::get('/shop',[ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product_slug}',[ShopController::class,'product_details'])->name('shop.product.details');

// Cart Routes
Route::get('/cart',[CartController::class,'index'])->name('cart.index');
Route::post('/cart/add',[CartController::class,'add_to_cart'])->name('cart.add');
Route::put('/cart/increase-quantity/{rowId}',[CartController::class,'increase_cart_quantity'])->name('cart.qty.increase');
Route::put('/cart/decrease-quantity/{rowId}',[CartController::class,'decrease_cart_quantity'])->name('cart.qty.decrease');
Route::delete('/cart/remove/{rowId}',[CartController::class,'remove_item'])->name('cart.item.remove');
Route::delete('/cart/clear',[CartController::class,'empty_cart'])->name('cart.empty');

Route::post('/cart/apply-coupon',[CartController::class,'apply_coupon_code'])->name('cart.coupon.apply');
Route::delete('/cart/remove-coupon',[CartController::class,'remove_coupon_code'])->name('cart.coupon.remove');

// Wishlist Routes
Route::post('/wishlist/add',[WishlistController::class,'add_to_wishlist'])->name('wishlist.add');
Route::get('/wishlist',[WishlistController::class,'index'])->name('wishlist.index');
Route::delete('/wishlist/item/remove/{rowId}',[WishlistController::class,'remove_item'])->name('wishlist.item.remove');
Route::delete('wishlist/clear',[WishlistController::class,'empty_wishlist'])->name('wishlist.items.clear');
Route::post('/wishlist/move-to-cart/{rowId}',[WishlistController::class,'move_to_cart'])->name('wishlist.move.to.cart');

// Checkout Routes
Route::get('/checkout',[CartController::class,'checkout'])->name('cart.checkout');
Route::post('/place-an-order',[CartController::class,'place_an_order'])->name('cart.place.an.order');
Route::get('/order-confirmation',[CartController::class,'order_confirmation'])->name('cart.order.confirmation');


// Auth and UserController Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
    Route::get('/account-orders',[UserController::class,'orders'])->name('user.orders');
    Route::get('/account-detail/{order_id}/details',[UserController::class,'order_details'])->name('user.order.details');
    Route::put('/account-order/cancel-order',[UserController::class,'order_cancel'])->name('user.order.cancel');
});


Route::middleware(['auth', AuthAdmin::class])->group(function () {

    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    //Brand Routes

    Route::get('/admin/brands', [BrandController::class, 'brands'])->name('admin.brands');

    Route::get('admin/brand/add', [BrandController::class, 'add_brand'])->name('admin.brand.add');

    Route::post('/admin/brand/store', [BrandController::class, 'brand_store'])->name('admin.brand.store');

    Route::get('/admin/brand/edit/{id}', [BrandController::class, 'brand_edit'])->name('admin.brand.edit');

    Route::put('/admin/brand/update', [BrandController::class, 'brand_update'])->name('admin.brand.update');

    Route::delete('/admin/brand/{id}/delete', [BrandController::class, 'brand_delete'])->name('admin.brand.delete');

    // Category Routes

    Route::get('/admin/categories', [CategoryController::class,'categories'])->name('admin.categories');

    Route::get('/admin/category/add', [CategoryController::class, 'category_add'])->name('admin.category.add');

    Route::post('/admin/category/store', [CategoryController::class,'category_store'])->name('admin.category.store');

    Route::get('/admin/category/{id}/edit', [CategoryController::class, 'category_edit'])->name('admin.category.edit');

    Route::put('/admin/category/update', [CategoryController::class, 'category_update'])->name('admin.category.update');

    Route::delete('/admin/category/{id}/delete', [CategoryController::class, 'category_delete'])->name('admin.category.delete');

    //  Prodcuts Routes

    Route::get('/admin/products',[ProductController::class,'products'])->name('admin.products');

    Route::get('/admin/product/add', [ProductController::class,'product_add'])->name('admin.product.add');

    Route::post('/admin/product/store', [ProductController::class, 'product_store'])->name('admin.product.store');

    Route::get('/admin/product/{id}/edit',[ProductController::class,'product_edit'])->name('admin.product.edit');

    Route::put('admin/product/update', [ProductController::class,'product_update'])->name('admin.product.update');

    Route::delete('/admin/product/{id}/delete', [ProductController::class, 'product_delete'])->name('admin.product.delete');

    // Coupons Routes

    Route::get('/admin/coupons',[CouponController::class,'coupons'])->name('admin.coupons');

    Route::get('/admin/coupon/add', [CouponController::class,'coupon_add'])->name('admin.coupon.add');

    Route::post('/admin/coupon/store', [CouponController::class, 'coupon_store'])->name('admin.coupon.store');

    Route::get('/admin/coupon/{id}/edit',[CouponController::class,'coupon_edit'])->name('admin.coupon.edit');

    Route::put('admin/coupon/update', [CouponController::class,'coupon_update'])->name('admin.coupon.update');

    Route::delete('/admin/coupon/{id}/delete', [CouponController::class, 'coupon_delete'])->name('admin.coupon.delete');

    // Order Routes
    Route::get('/admin/orders',[AdminController::class,'orders'])->name('admin.orders');
    Route::get('/admin/order/{order_id}/details',[AdminController::class,'order_details'])->name('admin.order.details');
    Route::put('/admin/order/update-status',[AdminController::class,'update_order_status'])->name('admin.order.status.update');
});
