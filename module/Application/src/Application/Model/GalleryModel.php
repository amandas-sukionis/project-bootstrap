<?php
namespace Application\Model;

class GalleryModel
{
    public function uploadImageFile($postData)
    {
        if (!file_exists('public/img/gallery')) {
            mkdir('public/img/gallery');
        }

        $oldName = $postData['uploadImageFile']['name'];
        $tmpFile = $postData['uploadImageFile']['tmp_name'];

        $newFileName = 'public/img/gallery/' . $oldName;
        $url = '/img/gallery/' . $oldName;
        if (file_exists($newFileName)) {
            $newFileName = 'public/img/gallery/' . date('Y-m-d') . '-' . $oldName;
            $url = '/img/gallery/' . date('Y-m-d') . '-' . $oldName;
        }

        move_uploaded_file($tmpFile, $newFileName);

        return $url;
    }
}