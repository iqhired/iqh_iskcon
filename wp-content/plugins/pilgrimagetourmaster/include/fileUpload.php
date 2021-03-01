<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['files'])) {
        $errors = [];
        $data = array();
        $path = '../../../uploads/documents/';
        $extensions = ['jpg', 'jpeg', 'png', 'gif'];

        $all_files = count($_FILES['files']['tmp_name']);


        for ($i = 0; $i < $all_files; $i++) {
            $file_name = $_FILES['files']['name'][$i];
            $file_tmp = $_FILES['files']['tmp_name'][$i];
            $file_type = $_FILES['files']['type'][$i];
            $file_size = $_FILES['files']['size'][$i];
            $file_ext = strtolower(end(explode('.', $_FILES['files']['name'][$i])));
            if(isset($_POST['name'][$i])){
                $file_name = $_POST['name'][$i];
                $fileNameArray = explode('/',$file_name);
                $file_name = $fileNameArray[1];

                if (!file_exists($path.$fileNameArray[0])) {
                    mkdir($path.$fileNameArray[0], 0777, true);
                }    
                $path = $path.$fileNameArray[0]."/";           
            }



            $file = $path . $file_name.'.'.$file_ext;

//            if (!in_array($file_ext, $extensions)) {
//                array_push($data , array_merge(array('status' => 'failed')));
//                array_push($data , array_merge(array('errorCode' => 'invalid-ext')));
//                echo json_encode($data);
//            }

            if ($file_size > 2097152) {
                $errors[] = 'File size exceeds limit: ' . $file_name . ' ' . $file_type;
                array_push($data , array_merge(array('status' => 'failed')));
                array_push($data , array_merge(array('errorCode' => 'file-exceeds-limit')));
                echo json_encode($data);
            }

            if (empty($errors)) {
                move_uploaded_file($file_tmp, $file);
                array_push($data , array_merge(array('status' => 'success')));
                echo json_encode($data);
            }
        }

        //if ($errors) print_r($errors);
    }
}