<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller {

    public function newsPage(Request $request) {
        $uid=$request->session()->get('uid');
        $privilege=User::where('uid',$uid)->first()->privilege;
        $user=UserController::GetInfo($uid);
        if(key_exists('Err',$user)){
            return redirect('/Action/Logout');
        }
        $news=News::paginate(10);
        $medias=[];
        $a=Media::all();
        foreach ($a as $media){
            $medias[$media->id]=["id"=>$media->id,"name"=>$media->name,"country"=>$media->country,"species"=>$media->species];
        }
        return view('news',["user"=>$user,"news"=>$news,"medias"=>$medias,"privilege"=>$privilege]);
    }

    public function newsDetail(Request $request,$id){
        $uid=$request->session()->get('uid');
        $privilege=User::where('uid',$uid)->first()->privilege;
        $user=UserController::GetInfo($uid);
        if(key_exists('Err',$user)){
            return redirect('/Action/Logout');
        }
        if (News::where('id',$id)->exists()) {
            $news=News::where('id',$id)->first();
            $news = $news->toArray();
            $news["created_at"] = date("Y-m-d H:i:s", strtotime($news["created_at"]));
        } else {
            return back();
        }
        $medias=[];
        $a=Media::all();
        foreach ($a as $media){
            $medias[$media->id]=["id"=>$media->id,"name"=>$media->name,"country"=>$media->country,"species"=>$media->species];
        }
        return view('news-detail',["user"=>$user,"news"=>$news,"medias"=>$medias,"privilege"=>$privilege]);

    }

    public function newsNew(Request $request){
        $uid=$request->session()->get('uid');
        $privilege=User::where('uid',$uid)->first()->privilege;
        $user=UserController::GetInfo($uid);
        if(key_exists('Err',$user)){
            return redirect('/Action/Logout');
        }

        return view('news-new',["user"=>$user,"privilege"=>$privilege]);
    }

}
