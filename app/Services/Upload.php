<?php
namespace Nourhan\Services;

use Nourhan\Database\DB;


class Upload
{

    public function __construct()
    {
    }

    public function upload($image, $user)
    {
        $result['success'] = false;
        $result['message'] = "";

        $target_dir = "images/";

        if (isset($image)) {
            $target_file = $target_dir . basename($image["name"]);
            $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

            $check = getimagesize($image["tmp_name"]);
            if ($check !== false) {
                $result['success'] = true;
                $result['message'] = "File is an image - " . $check["mime"] . ".";
            } else {
                $result['success'] = false;
                $result['message'] = "File is not an image.";
                $uploadOk = 0;
            }
        }
        // Check if file already exists
        if (file_exists($target_file)) {
            $result['success'] = false;
            $result['message'] = "Sorry, image already exists.";
        }
        // Check file size
        if ($image["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $result['success'] = false;
            $result['message'] = "Sorry, your image is too large.";
        }
        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif"
        ) {
            $result['success'] = false;
            $result['message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed..";
        }
// Check if $uploadOk is set to 0 by an error
        if ($result['success'] == false) {

            return $result;
// if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($image["tmp_name"], $target_file)) {
                $result['message'] = "The file " . basename($image["name"]) . " has been uploaded.";

            } else {
                $result['message'] = "Sorry, there was an error uploading your file";
            }
        }

        return $result;
    }

    public function uploadFile($file)
    {
        $target_dir = __DIR__ . "/../storage/";

        $target_file = $target_dir . basename($file["name"]);

        $fileType = pathinfo($target_file, PATHINFO_EXTENSION);

        $result['success'] = true;
        $result['message'] = "";

        // Allow certain file formats
        if ($fileType != "json") {
            $result['success'] = false;
            $result['message'] = "Sorry, only JSON files are allowed.";
        }

        // Check if $uploadOk is set to 0 by an error
        if ($result['success'] != true) {

            return $result;
        } else { // if everything is ok, try to upload file
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                $result['success'] = true;
                $result['message'] = "The file " . basename($file["name"]) . " has been uploaded.";
            } else {
                $result['success'] = false;
                $result['message'] = "Sorry, there was an error uploading your file.";
            }
        }

        return $result;
    }

    public function uploadCertificate($file)
    {
        $target_dir = __DIR__ . "/../storage/certificates/";

        $target_file = $target_dir . date("Y-m-d H:i:s"). "_".$_SESSION['user']['email']."_". basename($file["name"]);

        $fileType = pathinfo($target_file, PATHINFO_EXTENSION);

        $result['success'] = true;
        $result['message'] = "";

        // Allow certain file formats
        if ($fileType != "pdf") {
            $result['success'] = false;
            $result['message'] = "Sorry, only PDF files are allowed.";
        }

        // Check if $uploadOk is set to 0 by an error
        if ($result['success'] != true) {

            return $result;
        } else { // if everything is ok, try to upload file
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                $result['success'] = true;
                $result['message'] = "The file " . basename($file["name"]) . " has been uploaded.";
            } else {
                $result['success'] = false;
                $result['message'] = "Sorry, there was an error uploading your file.";
            }
        }

        return $result;
    }

}


?>