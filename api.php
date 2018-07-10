<?php

  header('Content-Type: application/json');

  /*
  |--------------------------------------------------------------------------
  | Getting Ready for Processing
  |--------------------------------------------------------------------------
  */

  require_once('constants.php');
  require_once('function.php');

  if(!isset($_POST['action']) && $_POST['privatetoken'] != API_SECRET) {
    $response = array("response" => "error", "remark" => "access denied");
    echo json_encode($response);
    exit();
  }
  $action = $_POST['action'];

  /*
  |--------------------------------------------------------------------------
  | Connect to Database
  |--------------------------------------------------------------------------
  */

  $conn = sqlsrv_connect(MSSQL_HOST , array( "Database" => MSSQL_DATABASE, "UID" => MSSQL_USER, "PWD" => MSSQL_PASS)) or die(print_r(sqlsrv_errors(), true));  

  /*
  |--------------------------------------------------------------------------
  | Processing Data
  |--------------------------------------------------------------------------
  |
  */

  /*
  |--------------------------------------------
  | signup
  |--------------------------------------------
  |
  | ID: signup
  | Method: POST
  | Description: Create user
  | Payload example:
  |   {
  |     "email" : "exmaple@example.com",
  |     "pass" : "secret"
  |   }
  | Output:
  |    {
  |      "response" : "success"
  |    }
  |
  */

  if($action === "signup") {
    if($_SERVER['REQUEST_METHOD'] != "POST") {
      $response = array("response" => "error", "remark" => "invalid method");
    }
    else {
      $data = json_decode($_POST['data'],true);
      $pass = password_hash(hash('sha256', $data['pass']), PASSWORD_DEFAULT);
      $email = $data['email'];
      $token = randomstring(32);
      $stmt = sqlsrv_prepare($conn, "INSERT INTO dbo.users (email, password_hash, token) VALUES (?, ?, ?)", array($email, $pass, $token)) or die(print_r(sqlsrv_errors(), true));
      sqlsrv_execute($stmt) or die(print_r(sqlsrv_errors(),true));
      $stmt = sqlsrv_prepare($conn, "INSERT INTO dbo.form2018 (token, student_name_1, student_phone_1, student_grade_1, student_img_1, student_doc_1, student_name_2, student_phone_2, student_grade_2, student_img_2, student_doc_2, student_name_3, student_phone_3, student_grade_3, student_img_3, student_doc_3, teacher_name, teacher_phone, teacher_img, school_name, status_status, submit_time) VALUES (?, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, 0, ?)", array($token, time()));
      sqlsrv_execute($stmt) or die(print_r(sqlsrv_errors(),true));
      $response = array("response" => "success");
    }
  }

  /*
  |--------------------------------------------
  | updatestudent
  |--------------------------------------------
  |
  | ID: updatestudent
  | Method: POST
  | Description: Add student data (if not exist before). If token already exists, please use updatestudent instead
  | External POST variable:
  |   - imgstd1,docstd1,imgstd2,docstd2,imgstd3,docstd3,imgtea
  | Payload example:
  |   {
  |     "grouptoken" : "randomstring",
  |     "student" : [
  |       {
  |         "name" : "Gusto Sonteen",
  |         "phone" : "0812345678",
  |         "grade" : 5
  |       },
  |       {
  |         "name" : "Gusto Sonteen",
  |         "phone" : "0812345678",
  |         "grade" : 5
  |       },
  |       {
  |         "name" : "Gusto Sonteen",
  |         "phone" : "0812345678",
  |         "grade" : 5
  |       }
  |     ],
  |    "teacher" : [
  |      {
  |         "name" : "Gusto Sonteen",
  |         "phone" : "0812345678"
  |      }
  |    ]
  |   }
  | Output:
  |    {
  |      "response" : "success"
  |    }
  |
  */

  else if($action === "updatestudent") {
    if($_SERVER['REQUEST_METHOD'] != "POST") {
      $response = array("response" => "error", "remark" => "invalid method");
    }
    else {
      $data = json_decode($_POST['data'],true);
      $token = $data['grouptoken'];
      $stmt = sqlsrv_prepare($conn, "SELECT id FROM dbo.users WHERE token LIKE ?", array($token)) or die(print_r(sqlsrv_errors(), true));
      $res = sqlsrv_execute($stmt) or die(print_r(sqlsrv_errors(), true));
      if($row = sqlsrv_fetch_rows($res)){
        $id = $row[0];
      }
      if(!isset($id)) {
        $response = array("response" => "error", "remark" => "token not found");
      }
      else {
        if($_FILES["imgstd1"]) {
          if(strtolower(end(explode('.', $_FILES['imgstd1']['name']))) == "png" || strtolower(end(explode('.', $_FILES['imgstd1']['name']))) == "jpg" || strtolower(end(explode('.', $_FILES['imgstd1']['name']))) == "jpeg") {
            $data["student"][0]["img"] = "storage/img/".$token."1".end(explode('.', $_FILES['imgstd1']['name']));
            move_uploaded_file($_FILES["imgstd1"]["tmp_name"], $data["student"][0]["img"]);
          }
          else {
            $remark = array_push($remark, array("from" => "img_student1", "status" => "invalid file extension"));
          }
        }
        else {
          $stmt = sqlsrv_prepare($conn, "SELECT student_img_1 FROM dbo.users WHERE token LIKE ?", array($token)) or die(print_r(sqlsrv_errors(), true));
          $res = sqlsrv_execute($stmt) or die(print_r(sqlsrv_errors(), true));
          if($row = sqlsrv_fetch_rows($res)) {
            $data["student"][0]["img"] = $row[0];
          }
        }
        if($_FILES["imgstd2"]) {
          if(strtolower(end(explode('.', $_FILES['imgstd2']['name']))) == "png" || strtolower(end(explode('.', $_FILES['imgstd2']['name']))) == "jpg" || strtolower(end(explode('.', $_FILES['imgstd2']['name']))) == "jpeg") {
            $data["student"][1]["img"] = "storage/img/".$token."1".end(explode('.', $_FILES['imgstd2']['name']));
            move_uploaded_file($_FILES["imgstd2"]["tmp_name"], $data["student"][1]["img"]);
          }
          else {
            $remark = array_push($remark, array("from" => "img_student2", "status" => "invalid file extension"));
          }
        }
        else {
          $stmt = sqlsrv_prepare($conn, "SELECT student_img_2 FROM dbo.users WHERE token LIKE ?", array($token)) or die(print_r(sqlsrv_errors(), true));
          $res = sqlsrv_execute($stmt) or die(print_r(sqlsrv_errors(), true));
          if($row = sqlsrv_fetch_rows($res)) {
            $data["student"][1]["img"] = $row[0];
          }
        }
        if($_FILES["imgstd3"]) {
          if(strtolower(end(explode('.', $_FILES['imgstd3']['name']))) == "png" || strtolower(end(explode('.', $_FILES['imgstd3']['name']))) == "jpg" || strtolower(end(explode('.', $_FILES['imgstd3']['name']))) == "jpeg") {
            $data["student"][2]["img"] = "storage/img/".$token."1".end(explode('.', $_FILES['imgstd3']['name']));
            move_uploaded_file($_FILES["imgstd3"]["tmp_name"], $data["student"][2]["img"]);
          }
          else {
            $remark = array_push($remark, array("from" => "img_student3", "status" => "invalid file extension"));
          }
        }
        else {
          $stmt = sqlsrv_prepare($conn, "SELECT student_img_3 FROM dbo.users WHERE token LIKE ?", array($token)) or die(print_r(sqlsrv_errors(), true));
          $res = sqlsrv_execute($stmt) or die(print_r(sqlsrv_errors(), true));
          if($row = sqlsrv_fetch_rows($res)) {
            $data["student"][2]["img"] = $row[0];
          }
        }
        if($_FILES["imgtea"]) {
          if(strtolower(end(explode('.', $_FILES['imgtea']['name']))) == "png" || strtolower(end(explode('.', $_FILES['imgtea']['name']))) == "jpg" || strtolower(end(explode('.', $_FILES['imgtea']['name']))) == "jpeg") {
            $data["teacher"][0]["img"] = "storage/img/".$token."4".end(explode('.', $_FILES['imgtea']['name']));
            move_uploaded_file($_FILES["imgtea"]["tmp_name"], $data["teacher"][0]["img"]);
          }
          else {
            $remark = array_push($remark, array("from" => "img_teacher1", "status" => "invalid file extension"));
          }
        }
        else {
          $stmt = sqlsrv_prepare($conn, "SELECT teacher_img_3 FROM dbo.users WHERE token LIKE ?", array($token)) or die(print_r(sqlsrv_errors(), true));
          $res = sqlsrv_execute($stmt) or die(print_r(sqlsrv_errors(), true));
          if($row = sqlsrv_fetch_rows($res)) {
            $data["teacher"][0]["img"] = $row[0];
          }
        }
        if($_FILES["docstd1"]) {
          if(strtolower(end(explode('.', $_FILES['docstd1']['name']))) == "png" || strtolower(end(explode('.', $_FILES['docstd1']['name']))) == "jpg" || strtolower(end(explode('.', $_FILES['docstd1']['name']))) == "jpeg" || strtolower(end(explode('.', $_FILES['docstd1']['name']))) == "pdf") {
            $data["student"][0]["doc"] = "storage/doc/".$token."1".end(explode('.', $_FILES['docstd1']['name']));
            move_uploaded_file($_FILES["docstd1"]["tmp_name"], $data["student"][0]["doc"]);
          }
          else {
            $remark = array_push($remark, array("from" => "doc_student1", "status" => "invalid file extension"));
          }
        }
        else {
          $stmt = sqlsrv_prepare($conn, "SELECT student_doc_1 FROM dbo.users WHERE token LIKE ?", array($token)) or die(print_r(sqlsrv_errors(), true));
          $res = sqlsrv_execute($stmt) or die(print_r(sqlsrv_errors(), true));
          if($row = sqlsrv_fetch_rows($res)) {
            $data["student"][0]["doc"] = $row[0];
          }
        }
        if($_FILES["docstd2"]) {
          if(strtolower(end(explode('.', $_FILES['docstd2']['name']))) == "png" || strtolower(end(explode('.', $_FILES['docstd2']['name']))) == "jpg" || strtolower(end(explode('.', $_FILES['docstd2']['name']))) == "jpeg" || strtolower(end(explode('.', $_FILES['docstd1']['name']))) == "pdf") {
            $data["student"][1]["doc"] = "storage/doc/".$token."1".end(explode('.', $_FILES['docstd2']['name']));
            move_uploaded_file($_FILES["docstd2"]["tmp_name"], $data["student"][1]["doc"]);
          }
          else {
            $remark = array_push($remark, array("from" => "doc_student2", "status" => "invalid file extension"));
          }
        }
        else {
          $stmt = sqlsrv_prepare($conn, "SELECT student_doc_2 FROM dbo.users WHERE token LIKE ?", array($token)) or die(print_r(sqlsrv_errors(), true));
          $res = sqlsrv_execute($stmt) or die(print_r(sqlsrv_errors(), true));
          if($row = sqlsrv_fetch_rows($res)) {
            $data["student"][1]["doc"] = $row[0];
          }
        }
        if($_FILES["docstd3"]) {
          if(strtolower(end(explode('.', $_FILES['docstd3']['name']))) == "png" || strtolower(end(explode('.', $_FILES['docstd3']['name']))) == "jpg" || strtolower(end(explode('.', $_FILES['docstd3']['name']))) == "jpeg" || strtolower(end(explode('.', $_FILES['docstd1']['name']))) == "pdf") {
            $data["student"][2]["doc"] = "storage/doc/".$token."1".end(explode('.', $_FILES['docstd3']['name']));
            move_uploaded_file($_FILES["docstd3"]["tmp_name"], $data["student"][2]["doc"]);
          }
          else {
            $remark = array_push($remark, array("from" => "doc_student3", "status" => "invalid file extension"));
          }
        }
        else {
          $stmt = sqlsrv_prepare($conn, "SELECT student_doc_3 FROM dbo.users WHERE token LIKE ?", array($token)) or die(print_r(sqlsrv_errors(), true));
          $res = sqlsrv_execute($stmt) or die(print_r(sqlsrv_errors(), true));
          if($row = sqlsrv_fetch_rows($res)) {
            $data["student"][2]["doc"] = $row[0];
          }
        }
        if(isset($remark)) {
          $response = array("response"=> "error", "remark" => $remark);
        }
        else {
          $stmt = sqlsrv_prepare($conn, "UPDATE dbo.form2018 SET student_name_1 = ?, student_phone_1 = ?, student_grade_1 = ?, student_img_1 = ?, student_doc_1 = ?, student_name_2 = ?, student_phone_2 = ?, student_grade_2 = ?, student_img_2 = ?, student_doc_2 = ?, student_name_3 = ?, student_phone_3 = ?, student_grade_3 = ?, student_img_3 = ?, student_doc_3 = ?, teacher_name = ?, teacher_phone = ?, teacher_img = ?, submit_time = ? WHERE token LIKE ?", array($data["student"][0]["name"], $data["student"][0]["phone"], $data["student"][0]["grade"], $data["student"][0]["img"], $data["student"][0]["doc"], $data["student"][1]["name"], $data["student"][1]["phone"], $data["student"][1]["grade"], $data["student"][1]["img"], $data["student"][1]["doc"], $data["student"][2]["name"], $data["student"][2]["phone"], $data["student"][2]["grade"], $data["student"][2]["img"], $data["student"][2]["doc"], $data["teacher"][0]["name"], $data["teacher"][0]["phone"], $data["teacher"][0]["img"], time(), $token));
          sqlsrv_execute($stmt) or die(print_r(sqlsrv_errors(),true));
          $response = array("response" => "success");
        }
      }
    }
  }

  /*
  |--------------------------------------------
  | getstudent
  |--------------------------------------------
  |
  | ID: getstudent
  | Method: POST
  | Description: Get student data
  | Payload example:
  |   {
  |     "grouptoken" : "randomstring"
  |   }
  | Output:
  |    {
  |     "response" : "success",
  |     "student" : [
  |       {
  |         "name" : "Gusto Sonteen",
  |         "phone" : "0812345678",
  |         "grade" : 5,
  |         "img" : "storage/img/1.jpg",
  |         "doc" : "storage/img/1.pdf"
  |       },
  |       {
  |         "name" : "Gusto Sonteen",
  |         "phone" : "0812345678",
  |         "grade" : 5,
  |         "img" : "storage/img/2.jpg",
  |         "doc" : "storage/img/2.pdf"
  |       },
  |       {
  |         "name" : "Gusto Sonteen",
  |         "phone" : "0812345678",
  |         "grade" : 5,
  |         "img" : "storage/img/3.jpg",
  |         "doc" : "storage/img/3.pdf"
  |       }
  |     ],
  |    "teacher" : [
  |      {
  |         "name" : "Gusto Sonteen",
  |         "phone" : "0812345678",
  |         "img" : "storage/img/4.jpg"
  |      }
  |    ],
  |    "timestamp" : "12345678",
  |    "school_name" : "Gusto Academy"
  |  }
  |
  */

  else if($action === "getdata") {
    if($_SERVER['REQUEST_METHOD'] != "POST") {
      $response = array("response" => "error", "remark" => "invalid method");
    }
    else {
      $data = json_decode($_POST['data'],true);
      $token = $data['grouptoken'];
      $stmt = sqlsrv_prepare($conn, "SELECT student_name_1, student_phone_1, student_grade_1, student_img_1, student_doc_1, student_name_2, student_phone_2, student_grade_2, student_img_2, student_doc_2, student_name_3, student_phone_3, student_grade_3, student_img_3, student_doc_3, teacher_name, teacher_phone, teacher_img, submit_time, school_name FROM dbo.users WHERE token LIKE ?", array($token)) or die(print_r(sqlsrv_errors(), true));
      $res = sqlsrv_execute($stmt) or die(print_r(sqlsrv_errors(), true));
      if($row = sqlsrv_fetch_rows($res)) {
        $is_executed = true;
        $students = array(
          array("name" => $row[0], "phone" => $row[1], "grade" => $row[2], "img" => $row[3], "doc" => $row[3]),
          array("name" => $row[4], "phone" => $row[5], "grade" => $row[6], "img" => $row[7], "doc" => $row[8]),
          array("name" => $row[9], "phone" => $row[10], "grade" => $row[11], "img" => $row[12], "doc" => $row[13])
        );
        $teacher = array(
          array("name" => $row[14], "phone" => $row[15], "img" => $row[16])
        );
        $timestamp = $row[17];
        $school_name = $row[18];
      }
      if(!isset($is_executed)) {
        $response = array("response" => "error", "remark" => "token not found");
      }
      else {
        $response = array("response" => "success", "student" => $students, "teacher" => $teacher, "timestamp" => $timestamp, "school_name" => $school_name);
      }
    }
  }

  /*
  |--------------------------------------------
  | login
  |--------------------------------------------
  |
  | ID: login
  | Method: POST
  | Description: Login
  | Payload example:
  |   {
  |     "email" : "exmaple@example.com",
  |     "pass" : "secret"
  |   }
  | Output:
  |    {
  |     "response" : "success",
  |     "grouptoken" : "randomstring"
  |    }
  |
  */

  else if($action === "login") {
    if($_SERVER['REQUEST_METHOD'] != "POST") {
      $response = array("response" => "error", "remark" => "invalid method");
    }
    else {
      $data = json_decode($_POST['data'],true);
      $pass = password_hash(hash('sha256', $data['pass']), PASSWORD_DEFAULT);
      $email = $data['email'];
      $stmt = sqlsrv_prepare($conn, "SELECT token FROM dbo.users WHERE email LIKE ? AND password_hash LIKE ?", array($email, $pass)) or die(print_r(sqlsrv_errors(), true));
      $res = sqlsrv_execute($stmt) or die(print_r(sqlsrv_errors(), true));
      if($row = sqlsrv_fetch_rows($res)){
        $token = $row[0];
      }
      if(!isset($token)) {
        $response = array("response" => "error", "remark" => "invalid credentials");
      }
      else {
        $response = array("response" => "success", "grouptoken" => $token);
      }
    }
  }

  /*
  |--------------------------------------------
  | updateschool
  |--------------------------------------------
  |
  | ID: updateschool
  | Method: POST
  | Description: Update school name
  | Payload example:
  |   {
  |     "grouptoken" : "randomstring",
  |     "school_name" : "Gusto Academy"
  |   }
  | Output:
  |    {
  |     "response" : "success"
  |    }
  |
  */

  else if($action === "updateschool") {
    if($_SERVER['REQUEST_METHOD'] != "POST") {
      $response = array("response" => "error", "remark" => "invalid method");
    }
    else {
      $data = json_decode($_POST['data'],true);
      $token = $data['grouptoken'];
      $school_name = $data['school_name'];
      $stmt = sqlsrv_prepare($conn, "UPDATE dbo.form2018 SET school_name = ?, submit_time = ? WHERE token LIKE ?", array($school_name, time(), $token));
      sqlsrv_execute($stmt) or die(print_r(sqlsrv_errors(),true));
      $response = array("response" => "success");
    }
  }

  /*
  |--------------------------------------------
  | reject
  |--------------------------------------------
  |
  | ID: -
  | Method: -
  | Description: Reject request if no actions exist
  | Output:
  |    {
  |     "response" : "error",
  |     "remark" : "invalid actions"
  |    }
  |
  */

  else {
    $response = array("response" => "error", "remark" => "invalid actions");
  }

  /*
  |--------------------------------------------------------------------------
  | Return Response
  |--------------------------------------------------------------------------
  */

  echo json_encode($response);

?>
