<?php

class GalleryModel
{
    public static function editImage($userId, $imageId, $newFilename)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        // Get the current filename
        $sql = "SELECT filename FROM images WHERE id = :id AND owner_id = :owner_id";
        $query = $database->prepare($sql);
        $query->execute(array(':id' => $imageId, ':owner_id' => $userId));
        $currentFilename = $query->fetchColumn();

        if ($currentFilename === false) {
            return array('status' => 'error', 'message' => 'Image not found');
        }

        // Rename the file
        $oldPath = Config::get('PATH_GALLERY') . $userId . '/' . $currentFilename;
        $newPath = Config::get('PATH_GALLERY') . $userId . '/' . $newFilename;
        if (!rename($oldPath, $newPath)) {
            return array('status' => 'error', 'message' => 'Failed to rename file');
        }

        // Update the filename in the database
        $sql = "UPDATE images SET filename = :filename WHERE id = :id AND owner_id = :owner_id";
        $query = $database->prepare($sql);
        $query->execute(array(':filename' => $newFilename, ':id' => $imageId, ':owner_id' => $userId));

        if ($query->rowCount() == 1) {
            return array('status' => 'success');
        } else {
            return array('status' => 'error', 'message' => 'Failed to update database');
        }
    }

    public static function getHashFromID($id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT hash FROM images WHERE id = :id";
        $query = $database->prepare($sql);
        $query->execute(array(':id' => $id));
        return $query->fetchColumn();
    }

    public static function uploadImage($userId, $imageFile)
    {
        if ($imageFile['error'] > 0) {
            // return json ajax error
            return array('status' => 'error', 'message' => 'Error uploading file');
        }

        // Create the directory if it doesn't exist
        $dir = $userId;
        if (!file_exists(Config::get('PATH_GALLERY') . $dir)) {
            mkdir(Config::get('PATH_GALLERY') . $dir, 0777, true);
        }

        // Save the image in the directory
        $filename = uniqid() . '_' . $imageFile['name'];
        $full_filename = Config::get('PATH_GALLERY') . $dir . '/' . $filename;

        move_uploaded_file($imageFile['tmp_name'], $full_filename);

        // Generate a unique hash for the image
        $hash = md5(uniqid(rand(), true));

        // Add a record to the images table
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("INSERT INTO images (owner_id, filename, file_size, hash) VALUES (:owner_id, :filename, :file_size, :hash)");
        $stmt->execute([':owner_id' => $userId, ':filename' => $filename, ':file_size' => filesize($full_filename), ':hash' => $hash]);
    }
    public static function getImages($userId)
    {
        // Get the image records from the database
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT * FROM images WHERE owner_id = :owner_id");
        $stmt->execute([':owner_id' => $userId]);
        // Fetch all images
        $images = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $images;
    }

    public static function getImage($userId, $imageId)
    {
        // Get the image record from the database
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT * FROM images WHERE id = :id AND owner_id = :owner_id");
        $stmt->execute([':id' => $imageId, ':owner_id' => $userId]);
        // Fetch the image
        $image = $stmt->fetch();
        return $image;
    }

    public static function getImageByHash($hash)
    {
        // Get the image record from the database
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT * FROM images WHERE hash = :hash");
        $stmt->execute([':hash' => $hash]);
        // Fetch the image
        $image = $stmt->fetch(PDO::FETCH_OBJ);
        return $image;
    }

    public static function displayImage($userId, $hash)
    {
        // Get the image record from the database
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT * FROM images WHERE hash = :hash");
        $stmt->execute([':hash' => $hash]);
        $image = $stmt->fetch();
        // Check if the image is shared or if the current user is the owner of the image
        if (!$image) {
            return;
        }

        if ($image->shared || $image->owner_id == $userId) {
            // Display the image
            header('Content-Type: image/jpeg');
            readfile(Config::get('PATH_GALLERY') . $image->owner_id . '/' . $image->filename);
        } else {
            // Display an error message
            echo 'You do not have permission to view this image.';
        }
    }

    public static function shareImage($userId, $imageId)
    {
        // Get the image record from the database
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT * FROM images WHERE id = :id AND owner_id = :owner_id");
        $stmt->execute([':id' => $imageId, ':owner_id' => $userId]);
        $image = $stmt->fetch();

        // Generate a unique hash for the image only if it doesn't exist
        $hash = $image->hash ? $image->hash : md5(uniqid(rand(), true));

        // Update the `shared` field in the images table
        $stmt = $db->prepare("UPDATE images SET shared = 1, hash = :hash WHERE id = :id AND owner_id = :owner_id");
        $stmt->execute([':id' => $imageId, ':owner_id' => $userId, ':hash' => $hash]);

        // Return the shareable link as an HTML hyperlink
        return Config::get('URL') . 'gallery/sharedImage/' . $hash;
    }

    public static function stopSharingImage($userId, $imageId)
    {
        // Update the `shared` field in the images table
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("UPDATE images SET shared = 0 WHERE id = :id AND owner_id = :owner_id");
        $stmt->execute([':id' => $imageId, ':owner_id' => $userId]);
    }


    public static function downloadImage($userId, $imageId)
    {
        // Get the image record from the database
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT * FROM images WHERE id = :id");
        $stmt->execute([':id' => $imageId]);
        $image = $stmt->fetch();

        // Check if the image is shared or if the current user is the owner of the image
        if ($image->shared || $image->owner_id == $userId) {
            // Increment the `download_count` field in the images table
            $stmt = $db->prepare("UPDATE images SET download_count = download_count + 1 WHERE id = :id");
            $stmt->execute([':id' => $imageId]);

            // Download the image
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($image->filename));
            readfile(Config::get('PATH_GALLERY') . $image->owner_id . '/' . $image->filename);
        } else {
            // Display an error message
            echo 'You do not have permission to download this image.';
        }
    }

    public static function deleteImage($userId, $imageId)
    {
        // Get the image record from the database
        $db = DatabaseFactory::getFactory()->getConnection();
        $stmt = $db->prepare("SELECT * FROM images WHERE id = :id AND owner_id = :owner_id");
        $stmt->execute([':id' => $imageId, ':owner_id' => $userId]);
        $image = $stmt->fetch();

        // Delete the image file from the server
        unlink(Config::get('PATH_GALLERY') . $image->owner_id . '/' . $image->filename);

        // Delete the corresponding record from the images table
        $stmt = $db->prepare("DELETE FROM images WHERE id = :id");
        $stmt->execute([':id' => $imageId]);
    }
}