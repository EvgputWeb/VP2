<?php

use Illuminate\Database\Eloquent\Model;

require_once 'User.php';


class File extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id','filename','comment'];


    public static function getFilesListOf($userId)
    {
        $filesList = self::query()
            ->where('user_id','=', $userId)->get()
            ->sortByDesc('filename')->toArray();
        return $filesList;
    }


    public static function saveUploadedFile($userId, $tmpFileName)
    {
        if (trim($tmpFileName) == '') {
            return;
        }

        $lastUploadedFile = self::query()
            ->where('user_id','=', $userId)
            ->get(['filename'])->sortByDesc('filename')->take(1)->toArray();

        // Переиндексация с нуля
        $lastUploadedFile = array_values($lastUploadedFile);

        if (empty($lastUploadedFile)) {
            $newFileName = 'photo_'.$userId.'_00001.jpg';
        } else {
            $num = explode('_',$lastUploadedFile[0]['filename']);
            $newNum = filter_var($num[2], FILTER_SANITIZE_NUMBER_INT) + 1;
            $newFileName = 'photo_'.$userId.'_'. str_pad($newNum, 5, '0', STR_PAD_LEFT) .'.jpg';
        }

        self::query()->create([
            'user_id' => $userId,
            'filename' => $newFileName
        ]);
    }

    /*
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
    } */



}
