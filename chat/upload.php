<?php
/*if ($_POST["label"]) {
    $label = $_POST["label"];
}*/
$allowedExts = array("gif", "jpeg", "jpg", "png");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = strtolower(end($temp));

if ((($_FILES["file"]["type"] == "image/gif")
        || ($_FILES["file"]["type"] == "image/jpeg")
        || ($_FILES["file"]["type"] == "image/jpg")
        || ($_FILES["file"]["type"] == "image/pjpeg")
        || ($_FILES["file"]["type"] == "image/x-png")
        || ($_FILES["file"]["type"] == "image/png"))
    && ($_FILES["file"]["size"] < 2000000)
    && in_array($extension, $allowedExts)) {
    if ($_FILES["file"]["error"] > 0) {
        echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
    } else {
	
//        $filename = $label.$_FILES["file"]["name"];
        $filename = $_FILES["file"]["name"];
    /*    echo "Upload: " . $_FILES["file"]["name"] . "<br>";
        echo "Type: " . $_FILES["file"]["type"] . "<br>";
        echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
        echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";
	*/
        if (file_exists("uploads/" . $filename)) {
            echo $filename . " already exists. ";
        } else {

            $userId = $_POST["userId"];
			$newFileName = "img/" . uniqid("img_") . "." . $extension;
            move_uploaded_file($_FILES["file"]["tmp_name"], $newFileName);
        //    echo "Stored in: " . "img/" . $filename;

            include_once "utils.php";
            setUserAvatarUrl($userId, $newFileName);

			echo $newFileName;
        }
    }
} else {
    echo "Invalid file. type: ".$_FILES["file"]["type"];
}