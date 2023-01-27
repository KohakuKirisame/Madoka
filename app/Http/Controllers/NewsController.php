<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Media;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller {

    public function newsPage(Request $request) {
        $uid=$request->session()->get('uid');
        $u=User::where('uid',$uid)->first();
        $privilege=$u->privilege;
        $user=UserController::GetInfo($uid);
        $user["media"]=$u->media;
        $user["country"]=$u->country;
        if(key_exists('Err',$user)){
            return redirect('/Action/Logout');
        }
        $news=News::orderBy("updated_at","desc")->paginate(10);
        $medias=[];
        $medias[0]=["id"=>0,"name"=>"国家声明","country"=>0,"species"=>0];
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
        $medias[0]=["id"=>0,"name"=>"国家声明","country"=>0,"species"=>0];
        $a=Media::all();
        foreach ($a as $media){
            $medias[$media->id]=["id"=>$media->id,"name"=>$media->name,"country"=>$media->country,"species"=>$media->species];
        }
        return view('news-detail',["user"=>$user,"news"=>$news,"medias"=>$medias,"privilege"=>$privilege]);

    }

    public function newsNew(Request $request){
        $uid=$request->session()->get('uid');
        $u=User::where('uid',$uid)->first();
        $privilege=$u->privilege;
        $user=UserController::GetInfo($uid);
        $user["media"]=$u->media;
        $user["country"]=$u->country;
        if(key_exists('Err',$user)){
            return redirect('/Action/Logout');
        }
        $medias=[];
        $a=Media::all();
        $medias[0]=["id"=>0,"name"=>"请选择媒体","country"=>0,"species"=>0];
        foreach ($a as $media){
            $medias[$media->id]=["id"=>$media->id,"name"=>$media->name,"country"=>$media->country,"species"=>$media->species];
        }
        $countries=[];
        $b=Country::all();
        $countries[0]=["id"=>0,"tag"=>"","name"=>"请选择国家"];
        foreach ($b as $country){
            $countries[$country->id]=["id"=>$country->id,"tag"=>$country->tag,"name"=>$country->name];
        }

        return view('news-new',["user"=>$user,"privilege"=>$privilege,"medias"=>$medias,"countries"=>$countries]);
    }

    public function newsSave(Request $request){
        $uid=$request->session()->get('uid');
        $privilege=User::where('uid',$uid)->first()->privilege;
        $title=$request->input('title');
        if($title==""){
            return back();
        }
        if($request->exists("content")){
            $content=$request->input('content');
        }else{
            $content=null;
        }
        $type=$request->input('type');
        if ($request->exists("newsid")){
            $news=News::where('id',intval($request->input('newsid')))->first();
            if ($privilege>1 &&($news->editor!=$uid||$news->status!=0)){
                return back();
            }
        }else{
            $news=new News();
            $news->editor=$uid;
        }

        $news->title=$title;
        $news->content=$content;
        $news->type=$type;
        if ($type!=2){
            $media=$request->input('media');
            if($media==0){
                return back();
            }
            $news->media=$media;
        }else{
            $news->media=0;
        }
        $news->status=0;
        $news->save();
        return redirect('/News');
    }

    public function newsEdit(Request $request,$id){
        $uid=$request->session()->get('uid');
        $u=User::where('uid',$uid)->first();
        $privilege=$u->privilege;
        $user=UserController::GetInfo($uid);
        $user["media"]=$u->media;
        $user["country"]=$u->country;
        if(key_exists('Err',$user)){
            return redirect('/Action/Logout');
        }

        if(News::where('id',$id)->exists()) {
            $news = News::where('id', $id)->first();
            if ($privilege>1 && ($news->editor != $uid || $news->status != 0)) {
                return back();
            }
            $news = $news->toArray();
        }else{
            return back();
        }
        $medias=[];
        $a=Media::all();
        $medias[0]=["id"=>0,"name"=>"请选择媒体","country"=>0,"species"=>0];
        foreach ($a as $media){
            $medias[$media->id]=["id"=>$media->id,"name"=>$media->name,"country"=>$media->country,"species"=>$media->species];
        }

        return view('news-new',["user"=>$user,"privilege"=>$privilege,"medias"=>$medias,"news"=>$news]);
    }

    public function newsPass(Request $request,$id){
        $uid=$request->session()->get('uid');
        $user=User::where('uid',$uid)->first();
        if ($user->privilege>1){
            return back();
        }
        $news=News::where('id',$id)->first();
        $news->status=1;
        $news->save();
        return back();
    }

}
