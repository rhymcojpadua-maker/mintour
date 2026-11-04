<?php
namespace App\Http\Controllers;
use App\Models\Product;
class ProductController extends Controller {
 public function index(){return view('public.products.index',['products'=>Product::published()->latest()->paginate(12)]);}
 public function show(Product $product){return view('public.products.show',compact('product'));}
}
