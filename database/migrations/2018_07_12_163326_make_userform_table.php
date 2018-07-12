<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeUserformTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('userform', function (Blueprint $table) {
            $table->increments('id');
            $table->text('token')->nullable();
            $table->text('student_name_1')->nullable();
            $table->text('student_phone_1')->nullable();
            $table->integer('student_grade_1')->nullable();
            $table->text('student_img_1')->nullable();
            $table->text('student_doc_1')->nullable();
            $table->text('student_name_2')->nullable();
            $table->text('student_phone_2')->nullable();
            $table->integer('student_grade_2')->nullable();
            $table->text('student_img_2')->nullable();
            $table->text('student_doc_2')->nullable();
            $table->text('student_name_3')->nullable();
            $table->text('student_phone_3')->nullable();
            $table->integer('student_grade_3')->nullable();
            $table->text('student_img_3')->nullable();
            $table->text('student_doc_3')->nullable();
            $table->text('teacher_name')->nullable();
            $table->text('teacher_phone')->nullable();
            $table->text('teacher_img')->nullable();
            $table->text('school_name')->nullable();
            $table->integer('status_status')->nullable();
            $table->timestamps();
            $table->SoftDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('userform');
    }
}
