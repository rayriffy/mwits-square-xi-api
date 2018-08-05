<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('signup', function (Request $request) {
    $request = $request->json()->all();

    if (empty($request['api_key']) || empty($request['data'])) {
        return ['response' => 'error', 'remark' => 'missing some/all payload'];
    }

    if ($request['api_key'] != env('API_KEY', 'riffydaddyallhome')) {
        return ['response' => 'error', 'remark' => 'access denined'];
    }

    $data = $request['data'];
    $pretoken = str_random(32);

    if (App\USER::where('email', $data['email'])->exists()) {
        return ['response' => 'error', 'remark' => 'email already exists'];
    }

    $user = new App\USER();
    $user->email = $data['email'];
    $user->password = Hash::make($data['pass']);
    $user->token = $pretoken;
    $user->save();

    $userdata = new App\USERFORM();
    $userdata->token = $pretoken;
    $userdata->status_status = 0;
    $userdata->save();

    return ['response' => 'success'];
});

Route::post('login', function (Request $request) {
    $request = $request->json()->all();

    if (empty($request['api_key']) || empty($request['data'])) {
        return ['response' => 'error', 'remark' => 'missing some/all payload'];
    }

    if ($request['api_key'] != env('API_KEY', 'riffydaddyallhome')) {
        return ['response' => 'error', 'remark' => 'access denined'];
    }

    $data = $request['data'];

    $login_result = App\USER::select('password', 'token')->where('email', $data['email'])->first();

    if ($login_result && Hash::check($data['pass'], $login_result->password)) {
        return ['response' => 'success', 'grouptoken' => $login_result->token];
    } else {
        return ['response' => 'error', 'remark' => 'invalid credentials'];
    }
});

Route::post('getdata', function (Request $request) {
    $request = $request->json()->all();

    if (empty($request['api_key']) || empty($request['data'])) {
        return ['response' => 'error', 'remark' => 'missing some/all payload'];
    }

    if ($request['api_key'] != env('API_KEY', 'riffydaddyallhome')) {
        return ['response' => 'error', 'remark' => 'access denined'];
    }

    $data = $request['data'];

    if (!(App\USERFORM::where('token', $data['grouptoken'])->exists())) {
        return ['response' => 'error', 'remark' => 'token not found'];
    }

    $query = App\USERFORM::where('token', $data['grouptoken'])->first();

    return [
        'students' => [
            [
                'name'  => $query['student_name_1'],
                'phone' => $query['student_phone_1'],
                'grade' => $query['student_grade_1'],
                'img'   => $query['student_img_1'],
                'doc'   => $query['student_doc_1'],
            ],
            [
                'name'  => $query['student_name_2'],
                'phone' => $query['student_phone_2'],
                'grade' => $query['student_grade_2'],
                'img'   => $query['student_img_2'],
                'doc'   => $query['student_doc_2'],
            ],
            [
                'name'  => $query['student_name_3'],
                'phone' => $query['student_phone_3'],
                'grade' => $query['student_grade_3'],
                'img'   => $query['student_img_3'],
                'doc'   => $query['student_doc_3'],
            ],
        ],
        'teacher' => [
            [
                'name'  => $query['teacher_name'],
                'phone' => $query['teacher_phone'],
                'img'   => $query['teacher_img'],
            ],
        ],
        'school' => [
            [
                'name' => $query['school_name'],
                'doc'  => $query['school_doc'],
            ],
        ],
        'timestamp' => $query['updated_at'],
    ];
});

Route::post('updateschool', function (Request $request) {
    $request = $request->json()->all();

    if (empty($request['api_key']) || empty($request['data'])) {
        return ['response' => 'error', 'remark' => 'missing some/all payload'];
    }

    if ($request['api_key'] != env('API_KEY', 'riffydaddyallhome')) {
        return ['response' => 'error', 'remark' => 'access denined'];
    }

    $data = $request['data'];

    if (App\USERFORM::where('token', $data['grouptoken'])->update(['school_name' => $data['school_name']])) {
        return ['response' => 'success'];
    } else {
        return ['response' => 'error', 'remark' => 'token not found'];
    }
});

Route::post('uploadschooldoc', function (Request $request) {
    $request = $request->json()->all();

    if (empty($request['api_key']) || empty($request['data'])) {
        return ['response' => 'error', 'remark' => 'missing some/all payload'];
    }

    if ($request['api_key'] != env('API_KEY', 'riffydaddyallhome')) {
        return ['response' => 'error', 'remark' => 'access denined'];
    }

    $data = $request['data'];

    $allowedDocEXT = ['jpg', 'png', 'jpeg', 'pdf'];

    if (isset($data['school'][0]['file']['doc'])) {
        if (!in_array(strtolower($data['school'][0]['file']['doc']['ext']), $allowedDocEXT)) {
            $remark[] = [
                'from'   => 'doc_school',
                'status' => 'invalid file extension',
            ];
        }
        Storage::disk('publicdoc')->put($data['grouptoken'].'.school.'.strtolower($data['school'][0]['file']['doc']['ext']), base64_decode($data['school'][0]['file']['doc']['base64']));
        $school[0]['doc'] = 'storage/doc/'.$data['grouptoken'].'.school.'.strtolower($data['school'][0]['file']['doc']['ext']);
    } else {
        $query = App\USERFORM::select('school_doc')->where('token', $data['grouptoken'])->first();
        $school[0]['doc'] = $query['school_doc'];
        unset($query);
    }

    if (isset($remark)) {
        return ['response'=> 'error', 'remark' => $remark];
    }

    $thingstoupdate = [
        'school_doc' => $school[0]['doc'],
    ];

    if (App\USERFORM::where('token', $data['grouptoken'])->update($thingstoupdate)) {
        return ['response' => 'success'];
    } else {
        return ['response' => 'error', 'remark' => 'cannot update form'];
    }
});

Route::post('updatedata', function (Request $request) {
    $request = $request->json()->all();

    if (empty($request['api_key']) || empty($request['data'])) {
        return ['response' => 'error', 'remark' => 'missing some/all payload'];
    }

    if ($request['api_key'] != env('API_KEY', 'riffydaddyallhome')) {
        return ['response' => 'error', 'remark' => 'access denined'];
    }

    $data = $request['data'];

    if (!(App\USER::where('token', $data['grouptoken'])->exists())) {
        return ['response' => 'error', 'remark' => 'token not found'];
    }

    $allowedImageEXT = ['jpg', 'png', 'jpeg'];
    $allowedDocEXT = ['jpg', 'png', 'jpeg', 'pdf'];

    if (isset($data['student'][0]['file']['image'])) {
        if (!in_array(strtolower($data['student'][0]['file']['image']['ext']), $allowedImageEXT)) {
            $remark[] = [
                'from'   => 'img_student1',
                'status' => 'invalid file extension',
            ];
        }
        Storage::disk('publicimage')->put($data['grouptoken'].'.1.'.strtolower($data['student'][0]['file']['image']['ext']), base64_decode($data['student'][0]['file']['image']['base64']));
        $student[0]['img'] = 'storage/image/'.$data['grouptoken'].'.1.'.strtolower($data['student'][0]['file']['image']['ext']);
    } else {
        $query = App\USERFORM::select('student_img_1')->where('token', $data['grouptoken'])->first();
        $student[0]['img'] = $query['student_img_1'];
        unset($query);
    }
    if (isset($data['student'][1]['file']['image'])) {
        if (!in_array(strtolower($data['student'][1]['file']['image']['ext']), $allowedImageEXT)) {
            $remark[] = [
                'from'   => 'img_student2',
                'status' => 'invalid file extension',
            ];
        }
        Storage::disk('publicimage')->put($data['grouptoken'].'.2.'.strtolower($data['student'][1]['file']['image']['ext']), base64_decode($data['student'][1]['file']['image']['base64']));
        $student[1]['img'] = 'storage/image/'.$data['grouptoken'].'.2.'.strtolower($data['student'][1]['file']['image']['ext']);
    } else {
        $query = App\USERFORM::select('student_img_2')->where('token', $data['grouptoken'])->first();
        $student[1]['img'] = $query['student_img_2'];
        unset($query);
    }
    if (isset($data['student'][2]['file']['image'])) {
        if (!in_array(strtolower($data['student'][2]['file']['image']['ext']), $allowedImageEXT)) {
            $remark[] = [
                'from'   => 'img_student3',
                'status' => 'invalid file extension',
            ];
        }
        Storage::disk('publicimage')->put($data['grouptoken'].'.3.'.strtolower($data['student'][2]['file']['image']['ext']), base64_decode($data['student'][2]['file']['image']['base64']));
        $student[2]['img'] = 'storage/image/'.$data['grouptoken'].'.3.'.strtolower($data['student'][2]['file']['image']['ext']);
    } else {
        $query = App\USERFORM::select('student_img_3')->where('token', $data['grouptoken'])->first();
        $student[2]['img'] = $query['student_img_3'];
        unset($query);
    }
    if (isset($data['teacher'][0]['file']['image'])) {
        if (!in_array(strtolower($data['teacher'][0]['file']['image']['ext']), $allowedImageEXT)) {
            $remark[] = [
                'from'   => 'img_teacher',
                'status' => 'invalid file extension',
            ];
        }
        Storage::disk('publicimage')->put($data['grouptoken'].'.4.'.strtolower($data['teacher'][0]['file']['image']['ext']), base64_decode($data['teacher'][0]['file']['image']['base64']));
        $teacher[0]['img'] = 'storage/image/'.$data['grouptoken'].'.4.'.strtolower($data['teacher'][0]['file']['image']['ext']);
    } else {
        $query = App\USERFORM::select('teacher_img')->where('token', $data['grouptoken'])->first();
        $teacher[0]['img'] = $query['teacher_img'];
        unset($query);
    }
    if (isset($data['student'][0]['file']['doc'])) {
        if (!in_array(strtolower($data['student'][0]['file']['doc']['ext']), $allowedDocEXT)) {
            $remark[] = [
                'from'   => 'doc_student1',
                'status' => 'invalid file extension',
            ];
        }
        Storage::disk('publicdoc')->put($data['grouptoken'].'.1.'.strtolower($data['student'][0]['file']['doc']['ext']), base64_decode($data['student'][0]['file']['doc']['base64']));
        $student[0]['doc'] = 'storage/doc/'.$data['grouptoken'].'.1.'.strtolower($data['student'][0]['file']['doc']['ext']);
    } else {
        $query = App\USERFORM::select('student_doc_1')->where('token', $data['grouptoken'])->first();
        $student[0]['doc'] = $query['student_doc_1'];
        unset($query);
    }
    if (isset($data['student'][1]['file']['doc'])) {
        if (!in_array(strtolower($data['student'][1]['file']['doc']['ext']), $allowedDocEXT)) {
            $remark[] = [
                'from'   => 'doc_student2',
                'status' => 'invalid file extension',
            ];
        }
        Storage::disk('publicdoc')->put($data['grouptoken'].'.2.'.strtolower($data['student'][1]['file']['doc']['ext']), base64_decode($data['student'][1]['file']['doc']['base64']));
        $student[1]['doc'] = 'storage/doc/'.$data['grouptoken'].'.2.'.strtolower($data['student'][1]['file']['doc']['ext']);
    } else {
        $query = App\USERFORM::select('student_doc_2')->where('token', $data['grouptoken'])->first();
        $student[1]['doc'] = $query['student_doc_2'];
        unset($query);
    }
    if (isset($data['student'][2]['file']['doc'])) {
        if (!in_array(strtolower($data['student'][2]['file']['doc']['ext']), $allowedDocEXT)) {
            $remark[] = [
                'from'   => 'doc_student3',
                'status' => 'invalid file extension',
            ];
        }
        Storage::disk('publicdoc')->put($data['grouptoken'].'.3.'.strtolower($data['student'][2]['file']['doc']['ext']), base64_decode($data['student'][2]['file']['doc']['base64']));
        $student[2]['doc'] = 'storage/doc/'.$data['grouptoken'].'.3.'.strtolower($data['student'][2]['file']['doc']['ext']);
    } else {
        $query = App\USERFORM::select('student_doc_3')->where('token', $data['grouptoken'])->first();
        $student[2]['doc'] = $query['student_doc_3'];
        unset($query);
    }

    if (isset($remark)) {
        return ['response'=> 'error', 'remark' => $remark];
    }

    $thingstoupdate = [
        'student_name_1'  => $data['student'][0]['name'],
        'student_phone_1' => $data['student'][0]['phone'],
        'student_grade_1' => $data['student'][0]['grade'],
        'student_img_1'   => $student[0]['img'],
        'student_doc_1'   => $student[0]['doc'],
        'student_name_2'  => $data['student'][1]['name'],
        'student_phone_2' => $data['student'][1]['phone'],
        'student_grade_2' => $data['student'][1]['grade'],
        'student_img_2'   => $student[1]['img'],
        'student_doc_2'   => $student[1]['doc'],
        'student_name_3'  => $data['student'][2]['name'],
        'student_phone_3' => $data['student'][2]['phone'],
        'student_grade_3' => $data['student'][2]['grade'],
        'student_img_3'   => $student[2]['img'],
        'student_doc_3'   => $student[2]['doc'],
        'teacher_name'    => $data['teacher'][0]['name'],
        'teacher_phone'   => $data['teacher'][0]['phone'],
        'teacher_img'     => $teacher[0]['img'],
    ];

    if (App\USERFORM::where('token', $data['grouptoken'])->update($thingstoupdate)) {
        return ['response' => 'success'];
    } else {
        return ['response' => 'error', 'remark' => 'cannot update form'];
    }
});

Route::post('iamfuckingdone', function (Request $request) {
    $request = $request->json()->all();

    if (empty($request['api_key']) || empty($request['data'])) {
        return ['response' => 'error', 'remark' => 'missing some/all payload'];
    }

    if ($request['api_key'] != env('API_KEY', 'riffydaddyallhome')) {
        return ['response' => 'error', 'remark' => 'access denined'];
    }

    $data = $request['data'];

    if (!(App\USERFORM::where('token', $data['grouptoken'])->exists())) {
        return ['response' => 'error', 'remark' => 'token not found'];
    }

    $check_column = [
        'student_name_1', 'student_phone_1', 'student_grade_1', 'student_img_1', 'student_doc_1',
        'student_name_2', 'student_phone_2', 'student_grade_2', 'student_img_2', 'student_doc_2',
        'student_name_3', 'student_phone_3', 'student_grade_3', 'student_img_3', 'student_doc_3',
        'teacher_name', 'teacher_phone', 'teacher_img', 'school_name', 'school_doc',
    ];
    $query = App\USERFORM::where('token', $data['grouptoken'])->first();

    foreach ($check_column as $ref) {
        if (empty($query[$ref]) || is_null($query[$ref])) {
            $remark[] = [
                'from'   => $ref,
                'status' => 'data seems empty',
            ];
        }
    }

    if (isset($remark)) {
        return ['response' => 'error', 'remark' => $remark];
    }

    if (App\USERFORM::where('token', $data['grouptoken'])->update(['status_status' => 1])) {
        return ['response' => 'success'];
    } else {
        return ['response' => 'error', 'remark' => 'cannot update form'];
    }
});

Route::post('deleteuser', function (Request $request) {
    $request = $request->json()->all();

    if (empty($request['api_key']) || empty($request['data'])) {
        return ['response' => 'error', 'remark' => 'missing some/all payload'];
    }

    if ($request['api_key'] != env('API_KEY', 'riffydaddyallhome')) {
        return ['response' => 'error', 'remark' => 'access denined'];
    }

    $data = $request['data'];

    if (!(App\USERFORM::where('token', $data['grouptoken'])->exists())) {
        return ['response' => 'error', 'remark' => 'token not found'];
    } else {
        App\USERFORM::where('token', $data['grouptoken'])->delete();

        return ['response' => 'success'];
    }
});
