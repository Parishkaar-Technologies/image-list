<?php

include 'db_connect.php'; // Include the connection

// uncomment the below for error log
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


        if ($uploadOk == 0) {

        } else {
            // echo "Target File: " . $targetFile . "<br>"; // Debugging: Print the full target path
            // var_dump($_FILES); // Debugging: Check $_FILES array (keep this for now)

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                try {
                    if ($imageType == "raw") {
                        
                        
                        $description = explode(",", $_POST['description']);
                            foreach ($description as $description) {
                                $description = trim($description);
                                $descriptionStmt = $pdo->prepare("SELECT description FROM images WHERE description = ?");
                        
                        
                        $stmt = $pdo->prepare("INSERT INTO images (unprocessed_image_url, raw_upload_date, description) VALUES (?, NOW(), ?)");
                        $stmt->execute([$targetFile, $description]);

                        $imageId = $pdo->lastInsertId();
                        
                        // if (isset($_POST['description'])) {
                        //     // $description = explode(",", $_POST['description']);
                        //     // foreach ($description as $description) {
                        //     //     $description = trim($description);
                        //     //     $descriptionStmt = $pdo->prepare("SELECT description FROM images WHERE description = ?");
                        //         $descriptionStmt->execute([$description]);
                        //         $descriptionResult = $descriptionStmt->fetch(PDO::FETCH_ASSOC);

                        //       if ($descriptionResult) {
                        //             $descriptionId = $descriptionResult['description'];
                        //         } else {
                        //             $insertdescriptionStmt = $pdo->prepare("INSERT INTO images (description) VALUES (?)");
                        //             $insertdescriptionStmt->execute([$description]);
                        //             // $descriptionId = $pdo->lastInsertId();
                        //         }
                        //         // $imgdescriptionStmt = $pdo->prepare("INSERT INTO images (description) VALUES (?)");
                        //         // $imgdescriptionStmt->execute([$descriptionId]);
                        //     }
                        }
                        
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
    <style>
        .gallery-item {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
    </style>
</head>

<body>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg" style="background-color: #B9F3FC;">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.html">a1 DB</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                <li class="nav-item">
                        <a class="nav-link" href="index.html">Image View</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="file.php">Image Upload</a>
                    </li>
                </ul>

            </div>
        </div>
    </nav>
    <div class="container">
        <h1>Image Upload</h1> 

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="raw-tab" data-bs-toggle="tab" href="#raw" role="tab" aria-controls="raw" aria-selected="true">Raw Images</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="processed-tab" data-bs-toggle="tab" href="#processed" role="tab" aria-controls="processed" aria-selected="false">Processed Images</a>
            </li>
        
        </ul>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="raw" role="tabpanel" aria-labelledby="raw-tab">
                <h2>Upload Raw Image</h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="file" name="image" id="image">
                    <input type="text" name="tags" placeholder="Enter tags (comma-separated)"><br><br>
                    <input type= "textarea" name="description" placeholder="Enter description">
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