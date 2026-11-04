<?php
namespace App\Http\Controllers;
use App\Models\Faq;
class FaqController extends Controller {
 public function index(){return view('public.faqs',['faqs'=>Faq::published()->orderBy('sort_order')->get()]);}
}
