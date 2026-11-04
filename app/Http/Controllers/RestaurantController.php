<?php
namespace App\Http\Controllers;
use App\Models\Restaurant;
class RestaurantController extends Controller {
 public function index(){return view('public.restaurants.index',['restaurants'=>Restaurant::published()->orderByDesc('rating_avg')->paginate(12)]);}
 public function show(Restaurant $restaurant){return view('public.restaurants.show',compact('restaurant'));}
}
