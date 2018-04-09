<?php

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public static $userDataFields = ['name', 'age', 'description', 'login', 'password', 'password-again'];


    public function CreateNewUser($userData)
    {
        $user = $this->query()->create([
            'name' => htmlspecialchars($userData['name']),
            'age' => intval($userData['age']),
            'description' => htmlspecialchars($userData['description']),
            'login' => htmlspecialchars($userData['login']),
            'password_hash' => password_hash($userData['password'], PASSWORD_BCRYPT)
        ]);
        // Отдаём userId
        return $user->id;
    }


    public function isLoginExists($login)
    {
        $user = $this->query()->whereRaw('lcase(login) = ?', strtolower($login))->get(['id'])->toArray();
        return (!empty($user));
    }


    public function getPasswordHash($login)
    {
        $user = $this->query()->whereRaw('lcase(login) = ?', strtolower($login))->get(['id', 'password_hash'])->toArray();
        return $user[0]['password_hash'];
    }


    public function getUserId($login)
    {
        $user = $this->query()->whereRaw('lcase(login) = ?', strtolower($login))->get(['id', 'password_hash'])->toArray();
        return $user[0]['id'];
    }


    public static function getUserInfoById($id)
    {
        $userInfo = [];
        $user = self::query()->find($id, ['name', 'login', 'age', 'description']);
        if (!empty($user)) {
            $user = $user->toArray();
            $userInfo['name'] = html_entity_decode($user['name']);
            $userInfo['login'] = html_entity_decode($user['login']);
            $userInfo['age'] = $user['age'];
            $userInfo['description'] = html_entity_decode($user['description']);
        }
        return $userInfo;
    }


    public static function encryptUserId($id, $password)
    {
        return openssl_encrypt($id, 'AES-128-ECB', $password);
    }


    public static function decryptUserId($cryptedId, $password)
    {
        return openssl_decrypt($cryptedId, 'AES-128-ECB', $password);
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


    public static function getUsersList($sort)
    {
        if ($sort == 'desc') {
            return self::all()->sortByDesc('age')->toArray();
        } else {
            return self::all()->sortBy('age')->toArray();
        }
    }
}
