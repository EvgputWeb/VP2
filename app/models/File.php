<?php

namespace EvgputWeb\MVC\Models;

use Illuminate\Database\Eloquent\Model;

use EvgputWeb\MVC\Core\Config;


class File extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id','filename','comment'];


    public static function getFilesListOf($userId)
    {
        $filesList = self::query()
            ->where('user_id','=', $userId)->get(['filename','comment'])
            ->sortByDesc('filename')->toArray();
        foreach ($filesList as $k => $item) {
            $filesList[$k]['comment'] = html_entity_decode($filesList[$k]['comment']);
            $filesList[$k]['comment'] = mb_ereg_replace('\n', '<br>', $filesList[$k]['comment']);
        }
        return $filesList;
    }


    public static function getLastUploadedFileName($userId)
    {
        $lastUploadedFile = self::query()
            ->where('user_id','=', $userId)
            ->get(['filename'])->sortByDesc('filename')->take(1)->toArray();
        if (!empty($lastUploadedFile)) {
            $lastUploadedFile = array_values($lastUploadedFile); // Переиндексация с нуля
        }
        return $lastUploadedFile;
    }


    public static function saveFile($userId, $fileName)
    {
        self::query()->create([
            'user_id' => $userId,
            'filename' => $fileName
        ]);
        return true;
    }


    public static function deleteFile($filename)
    {
        self::query()->where('filename', '=', $filename)->delete();
        return true;
    }


    public static function commentFile($filename, $comment)
    {
        self::query()->where('filename', '=', $filename)
            ->update([
                'comment' => htmlspecialchars($comment)
            ]);
        return true;
    }

}
