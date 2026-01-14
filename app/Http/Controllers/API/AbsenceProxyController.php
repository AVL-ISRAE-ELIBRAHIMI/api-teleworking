<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AbsenceProxyController extends Controller
{
    public function send(Request $request)
    {
        $response = Http::attach(
            'teleworking',
            file_get_contents($request->file('teleworking')->getRealPath()),
            $request->file('teleworking')->getClientOriginalName()
        )->attach(
            'pointage',
            file_get_contents($request->file('pointage')->getRealPath()),
            $request->file('pointage')->getClientOriginalName()
        )->attach(
            'leave',
            file_get_contents($request->file('leave')->getRealPath()),
            $request->file('leave')->getClientOriginalName()
        )->post('http://10.42.202.11:8000/absences');
                return $response->json();
    }
    
}
