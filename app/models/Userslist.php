<?php

use Illuminate\Database\Eloquent\Model;

require_once 'User.php';


class Userslist extends Model
{
    protected $table = 'users';


    public function getUsersList()
    {
        $users = User::all()->sortByDesc('id')->toArray();
        return $users;
    }


    public function deleteUser($userId)
    {
        User::destroy($userId);

        // Удаляем фотку, если она есть
        $photoFilename = Config::getPhotosFolder() . '/photo_' . intval($userId) . '.jpg';
        if (file_exists($photoFilename)) {
            unlink($photoFilename);
        }
        return true;
    }


    public function getUserData($userId)
    {
        return User::query()->find($userId)->toArray();
    }


    public function updateUserData($userId, $newData)
    {
        $email = strtolower($newData['email']);
        $user = User::query()->whereRaw('lcase(email) = ?', $email)->get(['id'])->toArray();
        if (!empty($user)) {
            if ($user[0]['id'] != $userId) {
                return 'Новый e-mail совпадает с e-mail другого пользователя';
            }
        }
        // Продолжаем ...
        User::query()->find($userId)->update([
            'name' => $newData['name'],
            'age' => $newData['age'],
            'description' => $newData['description'],
            'email' => $newData['email']
        ]);
        if (isset($newData['photo_filename'])) {
            // Обновляем фотографию
            if (User::saveUserPhoto($userId, $newData['photo_filename'])) {
                User::query()->find($userId)->update([
                    'photo_link' => "photo_$userId.jpg"
                ]);
            }
        }
        return true;
    }


    public function createNewUser($userData)
    {
        $newUser = new User;
        return $newUser->Register($userData, $id);
    }
}
