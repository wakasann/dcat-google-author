<?php

namespace Wakazunn\GoogleAuthor\Http\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Widgets\Form;
use Wakazunn\GoogleAuthor\GoogleAuthorServiceProvider;

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
        }

        if($clear == 1){
            Administrator::where('id',$user->id)->update([
                'google_auth' => NULL
            ]);
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
            $this->text('google_auth')->default($user->google_auth)->readOnly(true);
            $this->hidden('clear')->value(1);
            $this->confirm('确认提示','解绑当前Google验证码吗?');
        }else{
            
            $ga = new \PHPGangsta_GoogleAuthenticator();
            $secret = $ga->createSecret();
            $this->text('google_auth')->value($secret)->readOnly(true);
            $qrCodeUrl = $ga->getQRCodeGoogleUrl(urlencode(GoogleAuthorServiceProvider::setting('show_name')), $secret);
            $this->html("<img src='{$qrCodeUrl}'/>");
            $this->text('onecode','验证码');
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
