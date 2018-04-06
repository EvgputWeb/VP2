<?php

use Illuminate\Database\Eloquent\Model;
use Intervention\Image\ImageManagerStatic as Image;


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
            'name' => htmlspecialchars($userData['name']),
            'age' => intval($userData['age']),
            'description' => htmlspecialchars($userData['description']),
            'login' => htmlspecialchars($userData['login']),
            'password_hash' => password_hash($userData['password'], PASSWORD_BCRYPT)
        ]);
        // Записали юзера в базу
        $userId = $user->id;
        // Если есть фотка, то помещаем её в папку для фоток с именем "mainphoto_".$userId."jpg"
        if (isset($userData['photo_filename'])) {
            self::saveUserPhoto($userId, $userData['photo_filename']);
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
        $user = static::query()->find($id, ['name', 'login', 'age', 'description']);
        if (!empty($user)) {
            $user = $user->toArray();
            $userInfo['name'] = html_entity_decode($user['name']);
            $userInfo['login'] = html_entity_decode($user['login']);
            $userInfo['age'] = $user['age'];
            $userInfo['description'] = html_entity_decode($user['description']);
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


    public static function saveUserPhoto($userId, $tmpFileName)
    {
        if (empty($tmpFileName)) {
            return false;
        }
        Image::configure(array('driver' => 'gd'));
        $img = Image::make($tmpFileName);
        // Вырезаем область в пропорции 3x4
        $img->crop( round(0.75*$img->height()), $img->height());
        $img->crop( $img->width(), round(1.33333*$img->width()));
        if ($img->width()>300) {
            $img->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        // Сохраняем в папку с фотками пользователей
        if (!file_exists(Config::getPhotosFolder())) {
            mkdir(Config::getPhotosFolder(), 0777);
        }
        if (!file_exists(Config::getPhotosFolder().'/thumbs')) {
            mkdir(Config::getPhotosFolder().'/thumbs', 0777);
        }
        $img->save(Config::getPhotosFolder().'/mainphoto_'. $userId .'.jpg',90);
        // Удаляем временный файл
        unlink($tmpFileName);
        return true;
    }


    public static function updateInfo($newData)
    {
        self::query()->find($newData['userId'])->update([
            'name' => htmlspecialchars($newData['newName']),
            'age' => intval($newData['newAge']),
            'description' => htmlspecialchars($newData['newDescription']),
        ]);
        return true;
    }

    public static function getUsersList()
    {
        return self::all()->sortBy('age')->toArray();
    }

}
