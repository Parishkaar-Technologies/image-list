<?php

include 'db_connect.php'; // Include the connection


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