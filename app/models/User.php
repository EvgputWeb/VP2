<?php

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public static $userDataFields = ['name', 'age', 'description', 'login', 'password', 'password-again'];


    public function Register($userData)
    {
        // Проверка: логин должен быть уникальным
        $login = strtolower($userData['login']);
        $user = $this->query()->whereRaw('lcase(login) = ?', $login)->get(['id'])->toArray();
        if (!empty($user)) {
            return 'Пользователь с таким логином уже есть';
        }
        // Нет такого пользователя. Создаём.
        $user = $this->query()->create([
            'name' => $userData['name'],
            'age' => intval($userData['age']),
            'description' => $userData['description'],
            'login' => $userData['login'],
            'password_hash' => password_hash($userData['password'], PASSWORD_BCRYPT)
        ]);
        // Записали юзера в базу
        $userId = $user->id;
        // Если есть фотка, то помещаем её в папку для фоток с именем "photo_".$userId."jpg"
        if (isset($userData['photo_filename'])) {
            if (self::saveUserPhoto($userId, $userData['photo_filename'])) {
                $this->query()->find($userId)->update([
                    'photo_link' => "photo_$userId.jpg"
                ]);
            }
        }
        // Отдаём userId только что зарегистрированного пользователя
        return intval($userId);
    }


    public function Auth($userData)
    {
        $login = strtolower($userData['login']);
        $user = $this->query()->whereRaw('lcase(login) = ?', $login)->get(['id', 'password_hash'])->toArray();
        if (empty($user)) {
            return 'Пользователь с таким логином не найден';
        }
        // Логин найден - проверяем пароль
        if (password_verify($userData['password'], $user[0]['password_hash'])) {
            // Успешная авторизация - отдаём userId
            return intval($user[0]['id']);
        } else {
            return 'Неверный пароль';
        }
    }


    public static function encryptUserId($id)
    {
        return openssl_encrypt($id, 'AES-128-ECB', Config::getCookieCryptPassword());
    }

    public static function decryptUserId($cryptedId)
    {
        return openssl_decrypt($cryptedId, 'AES-128-ECB', Config::getCookieCryptPassword());
    }


    public static function getUserInfoById($id)
    {
        $userInfo = [];
        $user = static::query()->find($id, ['name', 'login']);
        if (!empty($user)) {
            $user = $user->toArray();
            $userInfo['name'] = $user['name'];
            $userInfo['login'] = $user['login'];
        }
        return $userInfo;
    }


    public static function getUserInfoByCookie()
    {
        $userInfo = [];
        $userInfo['authorized'] = false;
        if (!isset($_COOKIE['user_id'])) { // Это незалогиненный пользователь
            return $userInfo;
        }
        // Это авторизованный пользователь.
        // Возвращаем его имя и логин, которые берём из базы
        $userInfo['authorized'] = true;

        // Расшифровываем id пользователя из куки
        $cryptedUserId = $_COOKIE['user_id'];
        $userInfo['id'] = self::decryptUserId($cryptedUserId);

        $usrInf = self::getUserInfoById($userInfo['id']);

        if (empty($usrInf)) {
            // Упс... А пользователя такого нету...
            $userInfo = [];
            $userInfo['authorized'] = false;
            return $userInfo;
        }
        return array_merge($userInfo, $usrInf);
    }


    public static function saveUserPhoto($userId, $tmpFilename)
    {
        if (empty($tmpFilename)) {
            return false;
        }
        $imgTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
        $imgType = exif_imagetype($tmpFilename);
        if (!in_array($imgType, $imgTypes)) {
            // недопустимый тип файла
            return false;
        }
        switch ($imgType) {
            case IMAGETYPE_JPEG:
                $img = imagecreatefromjpeg($tmpFilename);
                break;
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($tmpFilename);
                break;
        }
        if ($img === false) {
            return false;
        }
        // обрезаем картинку - делаем квадрат
        $size = min(imagesx($img), imagesy($img));
        $img2 = imagecrop($img, ['x' => 0, 'y' => 0, 'width' => $size, 'height' => $size]);
        if ($img2 === false) {
            imagedestroy($img);
            return false;
        }
        imagedestroy($img);
        // масштабируем до размера 100x100
        $imageScaled = imagescale($img2, 100);
        if ($imageScaled === false) {
            imagedestroy($img2);
            return false;
        }

        // Сохраняем в папку с фотками пользователей
        $photoFilename = Config::getPhotosFolder() . '/photo_' . intval($userId) . '.jpg';
        imagejpeg($imageScaled, $photoFilename, 90);
        imagedestroy($imageScaled);

        // Удаляем временный файл
        unlink($tmpFilename);

        return true;
    }
}
