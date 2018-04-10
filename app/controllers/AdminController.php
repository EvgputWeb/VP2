<?php

namespace EvgputWeb\MVC\Controllers;

use EvgputWeb\MVC\Core\Config;
use EvgputWeb\MVC\Models\File;
use EvgputWeb\MVC\Models\User;

use Intervention\Image\ImageManagerStatic as Image;

class AdminController extends Controller
{
    private $userInfo;
    private $viewData;


    public function __construct()
    {
        parent::__construct();
        Image::configure(array('driver' => 'gd'));
    }


    private function checkAuth()
    {
        $this->userInfo = self::getUserInfoByCookie();
        if (!$this->userInfo['authorized']) {
            // Надо авторизоваться
            header('Location: /user/auth');
            die;
        }
        // Авторизованный пользователь - получаем его данные
        // для передачи во view
        $this->viewData['userId'] = $this->userInfo['id'];
        $this->viewData['login'] = $this->userInfo['login'];
        $this->viewData['name'] = $this->userInfo['name'];
        $this->viewData['age'] = $this->userInfo['age'];
        $this->viewData['description'] = mb_ereg_replace('\n', '<br>', $this->userInfo['description']);
    }


    public function actionIndex()
    {
        $this->checkAuth();
        $this->view->render('admin', $this->viewData);
    }


    public function actionMyFiles(array $params)
    {
        $this->checkAuth();

        if (count($params) > 0) {
            // Прилетели данные от пользователя
            if (isset($params['submit']) && (!empty($_FILES['photo']['tmp_name']))) {
                $this->saveUploadedFile($this->userInfo['id'], $_FILES['photo']['tmp_name']);
                $this->viewData['files'] = File::getFilesListOf($this->userInfo['id']);
                header('Location: /admin/myfiles'); // чтобы _POST и _FILES очистились
                return;
            }
        }
        // Показываем список файлов
        $this->viewData['files'] = File::getFilesListOf($this->userInfo['id']);
        $this->view->render('admin_files', $this->viewData);
    }


    private function saveUploadedFile($userId, $tmpFileName)
    {
        // Нужно дать файлу новое имя в формате photo_.$userId._NNNNN
        // где NNNNN - порядковый номер загруженного пользователем файла
        $lastUploadedFile = File::getLastUploadedFileName($userId);
        if (empty($lastUploadedFile)) {
            $newFileName = 'photo_' . $userId . '_00001';
        } else {
            $num = explode('_', $lastUploadedFile[0]['filename']);
            $newNum = filter_var($num[2], FILTER_SANITIZE_NUMBER_INT) + 1;
            $newFileName = 'photo_' . $userId . '_' . str_pad($newNum, 5, '0', STR_PAD_LEFT);
        }
        // Сохраняем в папку для фоток, а также в папку thumbs - миниатюру (для ускорения отдачи)
        $img = Image::make($tmpFileName);
        $img->resize($img->width(), $img->height());
        $img->save(Config::getPhotosFolder() . '/' . $newFileName . '.jpg', 90);
        $img->resize(200, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->save(Config::getPhotosFolder() . '/thumbs/' . $newFileName . '.jpg', 90);
        // Делаем запись в базу
        File::saveFile($userId, $newFileName);
        unlink($tmpFileName);
    }


    public function actionThumbs(array $params)
    {
        if (empty($params['request_from_url'])) {
            return;
        }
        $userInfo = self::getUserInfoByCookie();
        if (!$userInfo['authorized']) {
            // Не авторизованному - не отдаём
            header('HTTP/1.0 403 Forbidden');
            echo 'You are not authorized user!';
            return;
        }
        // Нужно отдать эскиз картинки
        $photoFilename = Config::getPhotosFolder() . '/thumbs/' . $params['request_from_url'] . '.jpg';
        if (file_exists($photoFilename)) {
            header("Content-Type: image/jpeg");
            header("Content-Length: " . filesize($photoFilename));
            echo file_get_contents($photoFilename);
        }
    }


    public function actionDeleteFile(array $params)
    {
        if (!isset($params['filename'])) {
            echo json_encode(['result' => 'fail', 'errorMessage' => 'Неверный запрос'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $userInfo = self::getUserInfoByCookie();
        if ($userInfo['authorized']) { // Это авторизованный пользователь - он имеет права на удаление
            // Вызываем у модели функцию удаления
            File::deleteFile($params['filename']);
            // Удаляем физически файл и thumb
            $deleteFileResult = $this->deleteFile($params['filename']);
            if ($deleteFileResult === true) {
                echo json_encode(['result' => 'success'], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['result' => 'fail', 'errorMessage' => $deleteFileResult], JSON_UNESCAPED_UNICODE);
            }
        } else {
            // Пользователь не авторизован - он не имеет прав
            echo json_encode(['result' => 'fail', 'errorMessage' => 'Вы не авторизованы. Нет прав на удаление'], JSON_UNESCAPED_UNICODE);
        }
    }


    private function deleteFile($filename)
    {
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


    public function actionCommentFile(array $params)
    {
        if (!isset($params['filename']) || !isset($params['comment'])) {
            echo json_encode(['result' => 'fail', 'errorMessage' => 'Неверный запрос'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $userInfo = self::getUserInfoByCookie();
        if ($userInfo['authorized']) { // Это авторизованный пользователь - он имеет права на комментирование
            // Вызываем у модели функцию комментирования
            $commentResult = File::commentFile($params['filename'], $params['comment']);
            if ($commentResult === true) {
                echo json_encode(['result' => 'success'], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['result' => 'fail', 'errorMessage' => $commentResult], JSON_UNESCAPED_UNICODE);
            }
        } else {
            // Пользователь не авторизован - он не имеет прав
            echo json_encode(['result' => 'fail', 'errorMessage' => 'Вы не авторизованы. Нет прав на комментирование'], JSON_UNESCAPED_UNICODE);
        }
    }


    public function actionViewFile(array $params)
    {
        if (empty($params['request_from_url'])) {
            return;
        }
        $userInfo = self::getUserInfoByCookie();
        if (!$userInfo['authorized']) {
            // Не авторизованному - не отдаём
            header('HTTP/1.0 403 Forbidden');
            echo 'You are not authorized user!';
            return;
        }
        // Нужно отдать картинку
        $photoFilename = Config::getPhotosFolder() . '/' . $params['request_from_url'] . '.jpg';
        if (file_exists($photoFilename)) {
            header("Content-Type: image/jpeg");
            header("Content-Length: " . filesize($photoFilename));
            echo file_get_contents($photoFilename);
        }
    }


    public function actionEditSelfData(array $params)
    {
        if (!isset($params['userId']) || !isset($params['newName']) || !isset($params['newAge'])) {
            echo json_encode(['result' => 'fail', 'errorMessage' => 'Неверный запрос'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $userInfo = self::getUserInfoByCookie();
        if ($userInfo['authorized']) { // Это авторизованный пользователь - он имеет права на редактирование
            if ($params['userId'] != $userInfo['id']) {  // Почему-то не совпало ...
                echo json_encode(['result' => 'fail', 'errorMessage' => 'Неверный запрос'], JSON_UNESCAPED_UNICODE);
                return;
            }
            // Вызываем у модели функцию редактирования
            $updateResult = User::updateInfo($params);
            if ($updateResult === true) {
                echo json_encode(['result' => 'success'], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['result' => 'fail', 'errorMessage' => $updateResult], JSON_UNESCAPED_UNICODE);
            }
        } else {
            // Пользователь не авторизован - он не имеет прав
            echo json_encode(['result' => 'fail', 'errorMessage' => 'Вы не авторизованы. Нет прав на редактирование данных'], JSON_UNESCAPED_UNICODE);
        }
    }


    public function actionUsersList(array $params)
    {
        $this->checkAuth();

        if (isset($params['request_from_url']) && ($params['request_from_url'] == 'desc')) {
            $this->viewData['users'] = User::getUsersList('desc');
        } else {
            $this->viewData['users'] = User::getUsersList('');
        }
        // Показываем список пользователей
        $this->view->render('admin_userslist', $this->viewData);
    }
}
