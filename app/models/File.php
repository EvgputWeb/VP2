<?php

use Illuminate\Database\Eloquent\Model;
use Intervention\Image\ImageManagerStatic as Image;

require_once 'User.php';


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


    public static function saveUploadedFile($userId, $tmpFileName)
    {
        $lastUploadedFile = self::query()
            ->where('user_id','=', $userId)
            ->get(['filename'])->sortByDesc('filename')->take(1)->toArray();

        // Переиндексация с нуля
        $lastUploadedFile = array_values($lastUploadedFile);

        if (empty($lastUploadedFile)) {
            $newFileName = 'photo_'.$userId.'_00001';
        } else {
            $num = explode('_',$lastUploadedFile[0]['filename']);
            $newNum = filter_var($num[2], FILTER_SANITIZE_NUMBER_INT) + 1;
            $newFileName = 'photo_'.$userId.'_'. str_pad($newNum, 5, '0', STR_PAD_LEFT);
        }

        self::query()->create([
            'user_id' => $userId,
            'filename' => $newFileName
        ]);

        Image::configure(array('driver' => 'gd'));
        $img = Image::make($tmpFileName);
        $img->resize($img->width(), $img->height());
        $img->save(Config::getPhotosFolder().'/'.$newFileName.'.jpg',90);
        $img->resize(200, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->save(Config::getPhotosFolder().'/thumbs/'.$newFileName.'.jpg',90);
    }


    public static function deleteFile($filename)
    {
        // Удаляем запись в таблице
        self::query()->where('filename', '=', $filename)->delete();
        // Удаляем файл и thumb
        $photoFilename = Config::getPhotosFolder() . '/' . $filename . '.jpg';
        $thumbFilename = Config::getPhotosFolder() . '/thumbs/' . $filename . '.jpg';
        $res = true;
        if (file_exists($photoFilename)) {
            $res = $res && unlink($photoFilename);
        }
        if (file_exists($thumbFilename)) {
            $res = $res && unlink($thumbFilename);
        }
        $res = $res ?: 'Ошибка при удалении файла';
        return $res;
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
