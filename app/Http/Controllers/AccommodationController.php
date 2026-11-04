<?php
namespace App\Http\Controllers;
use App\Models\Accommodation;
class AccommodationController extends Controller {
 public function index(){return view('public.accommodations.index',['accommodations'=>Accommodation::published()->orderByDesc('rating_avg')->paginate(12)]);}
 public function show(Accommodation $accommodation){return view('public.accommodations.show',compact('accommodation'));}
}
