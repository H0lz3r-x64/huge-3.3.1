<?php

class GalleryController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->View->render(
            'gallery/index',
            array(
                'images' => GalleryModel::getImages(Session::get('user_id'))
            )
        );
    }

    public function upload()
    {
        // Handle the image upload
        if (isset($_FILES['image'])) {
            GalleryModel::uploadImage(Session::get('user_id'), $_FILES['image']);
        }

    }

    public function share($id)
    {
        // Share the image
        $link = GalleryModel::shareImage(Session::get('user_id'), $id);
        // Display the shareable link
        echo $link;
    }

    public function stopShare($id)
    {
        // Stop sharing the image
        GalleryModel::stopSharingImage(Session::get('user_id'), $id);
        // Redirect back to the gallery
        header('Location: ' . Config::get('URL') . 'gallery/index');
    }

    public function getHashFromID($id)
    {
        // Get the image hash
        $hash = GalleryModel::getHashFromID($id);
        // Display the hash
        echo $hash;
    }

    public function display($hash)
    {
        // Display the image
        GalleryModel::displayImage(Session::get('user_id'), $hash);
    }

    public function sharedImage($hash)
    {
        $img = GalleryModel::getImageByHash($hash);
        // handle no image found
        if (!$img || $img->owner_id != Session::get('user_id') && $img->shared == 0) {
            Redirect::to('gallery/index');
            return;
        }

        // Display the shared image
        $this->View->render(
            'gallery/view',
            array(
                'image' => $img,
                'owner' => UserModel::getPublicProfileOfUser($img->owner_id)
            )
        );
    }

    public function download($id)
    {
        // Download the image
        GalleryModel::downloadImage(Session::get('user_id'), $id);
    }

    public function delete($id)
    {
        // Delete the image
        GalleryModel::deleteImage(Session::get('user_id'), $id);

        // Redirect back to the gallery
        header('Location: ' . Config::get('URL') . 'gallery/index');
    }

    public function edit($id)
    {
        $newFilename = $_POST['filename'];

        $result = GalleryModel::editImage(Session::get('user_id'), $id, $newFilename);

        // Return a JSON response
        header('Content-Type: application/json');
        echo json_encode($result);
    }


}