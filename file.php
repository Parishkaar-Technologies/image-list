<?php
include 'db_connect.php'; // Include the connection

// uncomment the below for error log Tarun is great
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


function uploadImage($pdo, $imageType)
{
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        if ($imageType == "raw") {
            $targetDir = "raw_images/";  // Raw image directory (relative path)
        } else {
            $targetDir = "processed_images/"; // Processed image directory (relative path)
        }

        $fileName = basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;

        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // ... (file type and size checks remain the same) ...

        if ($uploadOk == 0) {

        } else {
            // echo "Target File: " . $targetFile . "<br>"; // Debugging: Print the full target path
            // var_dump($_FILES); // Debugging: Check $_FILES array (keep this for now)

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                try {
                    if ($imageType == "raw") {
                        
                        
                        $stmt = $pdo->prepare("INSERT INTO images (unprocessed_image_url, raw_upload_date) VALUES (?, NOW())");
                        $stmt->execute([$targetFile]);

                        $imageId = $pdo->lastInsertId();

                        // Handle tags
                        if (isset($_POST['tags'])) {
                            $tags = explode(",", $_POST['tags']);
                            foreach ($tags as $tag) {
                                $tag = trim($tag);
                                $tagStmt = $pdo->prepare("SELECT tag_id FROM tags WHERE tag = ?");
                                $tagStmt->execute([$tag]);
                                $tagResult = $tagStmt->fetch(PDO::FETCH_ASSOC);

                               if ($tagResult) {
                                    $tagId = $tagResult['tag_id'];
                                } else {
                                    $insertTagStmt = $pdo->prepare("INSERT INTO tags (tag) VALUES (?)");
                                    $insertTagStmt->execute([$tag]);
                                    $tagId = $pdo->lastInsertId();
                                }
                                $imgTagStmt = $pdo->prepare("INSERT INTO img_tags (image_id, tag_id) VALUES (?, ?)");
                                $imgTagStmt->execute([$imageId, $tagId]);
                            }
                        }
                        

                    } else { // processed
                        $stmt = $pdo->prepare("UPDATE images SET processed_image_url = ?, processed_upload_date = NOW() WHERE img_id = ?");
                        $stmt->execute([$targetFile, $_POST['image_id']]);
                    }
                    echo "The file " . $fileName . " has been uploaded.";
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }
}



// Handle form submissions
if (isset($_POST["uploadRaw"])) {
    uploadImage($pdo, "raw");
}

if (isset($_POST["uploadProcessed"])) {
    uploadImage($pdo, "processed");
}

// Fetch images from the database (using PDO)
$rawImages = $pdo->query("SELECT * FROM images WHERE processed_image_url IS NULL");
$processedImages = $pdo->query("SELECT * FROM images WHERE processed_image_url IS NOT NULL");


?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Upload/Download</title>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet">
    
</head>

<body>

    <div class="container">
        <h1>Image Management</h1> 

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="raw-tab" data-bs-toggle="tab" href="#raw" role="tab" aria-controls="raw" aria-selected="true">Raw Images</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="processed-tab" data-bs-toggle="tab" href="#processed" role="tab" aria-controls="processed" aria-selected="false">Processed Images</a>
            </li>
            <li><a href="index.html" class="btn btn-primary">Image List</a></li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="raw" role="tabpanel" aria-labelledby="raw-tab">
                <h2>Upload Raw Image</h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="file" name="image" id="image">
                    <input type="text" name="tags" placeholder="Enter tags (comma-separated)"><br><br>
                    <input type= "textarea" name="descriptions" placeholder="Enter description">
                    <input type="submit" name="uploadRaw" value="Upload Raw Image" class="btn btn-primary">
                </form>
                <hr>
               
            </div>

            <div class="tab-pane fade" id="processed" role="tabpanel" aria-labelledby="processed-tab">
                <h2>Processed Images</h2>
                <div class="row">
                    <form method="post" enctype="multipart/form-data">
                        <input type="number" name="image_id" placeholder="Enter Image ID"  id="image_id" required><br><br> <input type="file" name="image" id="image" required><br><br>
                        <input type="submit" name="uploadProcessed" value="Upload Processed Image" class="btn btn-primary">
                    </form>
                </div>
            </div>

            
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>