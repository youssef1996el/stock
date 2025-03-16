<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AchatController extends Controller
{
    public function index()
    {
        return view('Achat.index');
    }
}
