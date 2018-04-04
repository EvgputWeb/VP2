<?php

use Illuminate\Database\Eloquent\Model;

require_once 'User.php';


class Fileslist extends Model
{
    protected $table = 'users';


    public function getFilesList()
    {
        $filesList = [];
        $idsList = User::all(['id'])->sortByDesc('id')->toArray();

        if (!empty($idsList)) {
            foreach ($idsList as $item) {
                $photoFilename = Config::getPhotosFolder() . '/photo_' . intval($item['id']) . '.jpg';
                if (file_exists($photoFilename)) {
                    $filesList[$item['id']] = 'photo_' . intval($item['id']) . '.jpg';
                }
            }
        }
        return $filesList;
    }


    public function deletePhoto($userId)
    {
        // Удаляем фотку, если она есть
        $photoFilename = Config::getPhotosFolder() . '/photo_' . intval($userId) . '.jpg';
        if (file_exists($photoFilename)) {
            if (unlink($photoFilename)) {
                User::query()->find($userId)->update([
                    'photo_link' => null
                ]);
                return true;
            } else {
                return 'Ошибка при удалении файла';
            }
        } else {
            return 'Файл не найден';
        }
    }
}
