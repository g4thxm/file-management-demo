<?php

namespace App\Http\Middleware;

//use App\Models\SubscriptionMenu;
use App\Models\Permission;
use App\Models\Submenu;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;


class IsPermitted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if ($request->ajax() || $request->wantsJson()) {
            return $next($request);
        }

        $user=Auth::id();
        $link =Request::segment(1);



        $permissiontable=(new Permission)->getTable();
        $submenu=(new Submenu)->getTable();
//        $setting=(new Settings)->getTable();
//        $subscription=(new SubscriptionMenu)->getTable();

        $data=DB::table($permissiontable)
            ->join($submenu,"$submenu.id","=","$permissiontable.submenu_id")
//            ->join($subscription,"$subscription.submenu_id","=","$submenu.id")
//            ->join($setting,"$setting.sub_type","=","$subscription.subscription_id")
            ->where("$permissiontable.user_id",$user)
            ->where("$submenu.link",$link);
        $data=$data->select("$submenu.id")->get();
       
        if (count($data) >= 1) {
            // dd(Crypt::encryptString('2026-01-15'));
            try {
                $endate = env('DATE');
                $edate = Crypt::decryptString($endate);
            } catch (\Throwable $th) {
                $edate = "2026-01-15";
            }

            $now = date('Y-m-d');
            $edate = date('Y-m-d', strtotime($edate));
            return $next($request);
        } else {
            return redirect('/');
        }
    }
}
