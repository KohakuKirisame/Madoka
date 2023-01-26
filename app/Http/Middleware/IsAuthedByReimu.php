<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class IsAuthedByReimu {

    public function handle(Request $request, Closure $next){

        if(!$request->session()->exists("uid")||$request->session()->get("valid")<time()){
            $request->session()->flush();
            return redirect("/Login");
        }

        return $next($request);
    }

}
