<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertAdminstratorGoogleAuthTable extends Migration
{
    public function getConnection()
    {
        return config('database.connection') ?: config('database.default');
    }

    public function config($key)
    {
        return config('admin.'.$key);
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->config('database.users_table'), function (Blueprint $table) {
            if(!Schema::hasColumn($this->config('database.users_table'),'google_auth')){
                $table->string('google_auth')->nullable()->comment('谷歌密钥');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->config('database.users_table'), function (Blueprint $table) {
            if(Schema::hasColumn($this->config('database.users_table'),'google_auth')){
                $table->dropColumn(['google_auth']);
            }
        });
    }
}
