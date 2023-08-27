<?php

namespace Wakazunn\GoogleAuthor\Traits;

use Dcat\Admin\Admin;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Widgets\Form;
use Wakazunn\GoogleAuthor\GoogleAuthorServiceProvider;
use Illuminate\Support\Facades\Cache;
trait WakaAuthForms
{

    function form_valid($input){
        $user = Admin::user();
        $code = Cache::get('waka_admin_google_author_code_'.$user->id);
        if($code){
            $captcha = $input['google_author_code'];
            if(empty($captcha) || strlen($captcha) > 6){
                return $this->response()->error('Google验证码错误，请重试');
            }
            $user = Admin::user();
            $ga = new \PHPGangsta_GoogleAuthenticator();
            $result =  $ga->verifyCode($code, $captcha);
            if(!$result){
                return $this->response()->error('Google验证码错误，请重试');
            }
        }
        

    }

    function form_inline(){
        $this->text('google_author_code','Google验证码')->required();
    }

    function form_row(){
        $this->row(function ($row) {
            $row->text('google_author_code','Google验证码')->required();

        });
    }
}