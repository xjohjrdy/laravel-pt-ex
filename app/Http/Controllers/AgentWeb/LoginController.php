<?php

namespace App\Http\Controllers\AgentWeb;


use App\Entitys\App\ShopSupplierUsers;
use App\Http\Controllers\Controller;
use App\Services\Common\NewSms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    public function loginWeb(Request $request)
    {

        if ($request->getHost() != 'suppliers.36qq.com') {
            return redirect('https://baidu.com', 301);
        }


        if ($request->session()->has('users')) {

            return redirect('agent_admin');
        }

        return view('agent.login');
    }
    public function logout(Request $request)
    {

        $request->session()->flush();

        return redirect('agent_login');
    }
    public function sendCode(Request $request)
    {
        $post_data = $request->all();

        $validator = Validator::make($post_data, [
            'phone' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->getInfoResponse(4001, '错误请求');
        }

        $phone = $post_data['phone'];

        if (!ShopSupplierUsers::where('phone', $phone)->exists()) {
            return $this->getInfoResponse(2001, '您的手机号没有权限登录后台！');
        }

        if (Cache::has($phone)) {
            return $this->getInfoResponse(4001, '验证码已发送!' . Cache::get($phone));
        }
        $res = mt_rand(100000, 999999);
        $new_sms = new NewSms();
        $res_sms = $new_sms->SendSms($phone, $res);
        Cache::put($phone, $res, 6);
        return $this->getResponse('发送成功');
    }
    public function login(Request $request)
    {
        $post_data = $request->all();

        $validator = Validator::make($post_data, [
            'phone' => 'required|numeric',
            'code' => 'required|numeric',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->getInfoResponse(4001, '错误请求');
        }

        $phone = $post_data['phone'];
        $code = $post_data['code'];
        $password = $post_data['password'];

        if (!Cache::has($phone)) {
            return $this->getInfoResponse(2001, '验证码已过期，请重新获取!');
        }

        if (Cache::get($phone) != $code) {
            return $this->getInfoResponse(2001, '验证码错误，请核对!');
        }


        $obj_user_info = ShopSupplierUsers::where('phone', $phone)->first();
        if (empty($obj_user_info)) {
            return $this->getInfoResponse(2001, '异常用户，请联系管理员！');
        }

        $visa_pwd = $obj_user_info->password;

        if (empty($visa_pwd)) {
            $obj_user_info->password = bcrypt($password);
        } elseif (!Hash::check($password, $visa_pwd)) {
            return $this->getInfoResponse(2001, '密码输入错误!');
        }

        $last_ip = $obj_user_info->last_ip;
        $last_time = $obj_user_info->last_time;
        $name = $obj_user_info->info;
        $phone = $obj_user_info->phone;
        $now_ip = $request->ip();
        $supplier_id = $obj_user_info->id;

        $obj_user_info->last_ip = $request->ip();
        $obj_user_info->last_time = time();
        $obj_user_info->save();

        $request->session()->put('users', [
            'supplier_id' => $supplier_id,
            'now_ip' => $now_ip,
            'last_ip' => $last_ip,
            'last_time' => $last_time,
            'name' => $name,
            'phone' => $phone
        ]);


        return $this->getResponse('登录成功！');


    }
}
