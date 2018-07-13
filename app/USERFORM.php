<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class USERFORM extends Model
{
    //

    use SoftDeletes;

    protected $table = 'userform';

    protected $primaryKey = 'id';

    protected $fillable = [
        'token',
        'student_name_1',
        'student_phone_1',
        'student_grade_1',
        'student_img_1',
        'student_doc_1',
        'student_name_2',
        'student_phone_2',
        'student_grade_2',
        'student_img_2',
        'student_doc_2',
        'student_name_3',
        'student_phone_3',
        'student_grade_3',
        'student_img_3',
        'student_doc_3',
        'teacher_name',
        'teacher_phone',
        'teacher_img',
        'school_name',
        'status_status'
];

    protected $hidden = [
        'created_at'
    ];

    protected $dates = ['deleted_at'];
}
