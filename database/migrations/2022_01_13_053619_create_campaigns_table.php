<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index('user_id_fk');
            $table->string('title');
            $table->string('url');
            $table->string('location');
            $table->string('city');
            $table->unsignedInteger('target_amount');
            $table->unsignedInteger('current_amount')->default(0);
            $table->timestamp('act_date')->useCurrent();
            $table->timestamp('deadline')->useCurrent();
            $table->string('banner_img');
            $table->text('description');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
}
