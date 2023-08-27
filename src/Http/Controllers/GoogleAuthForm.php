<?php

namespace Wakazunn\GoogleAuthor\Http\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Widgets\Form;
use Wakazunn\GoogleAuthor\GoogleAuthorServiceProvider;
use Illuminate\Support\Facades\Cache;
class GoogleAuthForm extends Form
{
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {

        $user = Admin::user();

        $oneCode = isset($input['onecode'])?$input['onecode']:'';
        $googleAuth = isset($input['google_auth'])?$input['google_auth']:'';

        $clear = $input['clear'];

        if(empty($user->google_auth)){
            if(empty($oneCode)){
                $this
				->response()
				->error('请输入6位 Google验证码');
            }
            $ga = new \PHPGangsta_GoogleAuthenticator();
            $checkResult = $ga->verifyCode($googleAuth, $oneCode, 2);    // 2 = 2*30sec clock tolerance
            if(!$checkResult){
                return $this->response()->error('Google 验证码绑定失败');
            }

            Administrator::where('id',$user->id)->update([
                'google_auth' => $googleAuth
            ]);
            Cache::forever('waka_admin_google_author_code_'.$user->id, $googleAuth);
        }else if($clear == 1){
            if($googleAuth != $user->google_auth){
                $this
				->response()
				->error('操作不正确');
            }
            $unbindCode = isset($input['google_auth_code'])?$input['google_auth_code']:'';
            if(empty($unbindCode)){
                $this
				->response()
				->error('请输入6位 Google验证码');
            }
            $googleAuth = $user->google_auth;
            $ga = new \PHPGangsta_GoogleAuthenticator();
            $checkResult = $ga->verifyCode($googleAuth, $unbindCode, 2);    // 2 = 2*30sec clock tolerance
            if(!$checkResult){
                return $this->response()->error('Google 验证码解绑失败');
            }
        }

        if($clear == 1){
            Administrator::where('id',$user->id)->update([
                'google_auth' => NULL
            ]);
            Cache::forget('waka_admin_google_author_code_'.$user->id);
        }
        if($clear == 1){
            return $this->response()->success('解绑成功')->refresh();
        }else{
            return $this
				->response()
				->success('绑定成功')
				->refresh();
        }
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $user = Admin::user();
        $this->display('id','后台用户')->value($user->name);
          // 创建谷歌验证码
        if(!empty($user->google_auth)){
            // $this->text('google_auth')->default($user->google_auth)->readOnly(true);
            $this->text('google_auth_code','验证码')->required();
            $this->hidden('clear')->value(1);
            $this->hidden('google_auth')->value($user->google_auth);
            $this->confirm('确认提示','解绑当前Google验证码吗?');
        }else{
            
            $ga = new \PHPGangsta_GoogleAuthenticator();
            $secret = $ga->createSecret();
            $this->text('google_auth')->value($secret)->readOnly(true);
            $qrCodeUrl = $ga->getQRCodeGoogleUrl(urlencode(GoogleAuthorServiceProvider::setting('show_name')), $secret);
            $this->html("<img src='{$qrCodeUrl}'/>");
            $this->text('onecode','验证码')->required();
            $this->hidden('clear')->value(0);
        }
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [
            'name'  => 'John Doe',
            'email' => 'John.Doe@gmail.com',
            
        ];
    }
}
