<?php
namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('public.dashboard', [
            'favorites' => $user->favorites()->with('destination.category')->latest()->get(),
            'reviews'   => $user->reviews()->with('destination')->latest()->get(),
            'sessions'  => $user->chatSessions()->withCount('messages')->latest()->take(10)->get(),
        ]);
    }
}
