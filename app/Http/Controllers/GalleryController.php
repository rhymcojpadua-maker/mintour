<?php
namespace App\Http\Controllers;
use App\Models\DestinationPhoto;
class GalleryController extends Controller {
 public function index(){return view('public.gallery',['photos'=>DestinationPhoto::with('destination')->orderByDesc('id')->paginate(24)]);}
}
