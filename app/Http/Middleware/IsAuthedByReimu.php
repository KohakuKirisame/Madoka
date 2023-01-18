<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class IsAuthedByReimu {

    public function handle(Request $request, Closure $next){

        if(!$request->session()->exists("uid")||$request->session()->get("valid")<time()){
            return redirect($_ENV["REIMU_URL"]."/login?app=madoka&redirect=https://madoka.nbmun.cn/Action/Login");
        }

        return $next($request);
    }

}
