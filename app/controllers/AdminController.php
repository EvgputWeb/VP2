<?php

require_once 'Controller.php';
require_once APP . '/models/User.php';
require_once APP . '/models/File.php';


class AdminController extends Controller
{
    private $userInfo;
    private $viewData;


    private function checkAuth()
    {
        $this->userInfo = User::getUserInfoByCookie();
        if (!$this->userInfo['authorized']) {
            // Надо авторизоваться
            header('Location: /user/auth');
            die;
        }
        // Авторизованный пользователь - получаем его данные
        // для передачи во view
        $this->viewData['login'] = $this->userInfo['login'];
        $this->viewData['name'] = $this->userInfo['name'];
    }


    public function actionIndex()
    {
        $this->checkAuth();
        $this->view->render('admin', $this->viewData);
    }


    public function actionMyFiles(array $params)
    {
        $this->checkAuth();

        if (count($params) == 0) {
            // Показываем список файлов
            $this->viewData['files'] = File::getFilesListOf($this->userInfo['id']);
            $this->view->render('admin_files', $this->viewData);
        } else {
            // Прилетели данные от пользователя
            if (isset($params['submit']) && (isset($_FILES['photo']['tmp_name']))) {
                File::saveUploadedFile($this->userInfo['id'],$_FILES['photo']['tmp_name']);
                unlink($_FILES['photo']['tmp_name']);
            }
            $this->viewData['files'] = File::getFilesListOf($this->userInfo['id']);
            header('Location: /admin/myfiles');
        }
    }


    public function actionThumbs(array $params)
    {
        if (empty($params['request_from_url'])) {
            return;
        }
        $userInfo = User::getUserInfoByCookie();
        if (!$userInfo['authorized']) {
            // Не авторизованному - не отдаём
            header('HTTP/1.0 403 Forbidden');
            echo 'You are not authorised user!';
            return;
        }
        // Нужно отдать картинку
        $photoFilename = Config::getPhotosFolder() . '/thumbs/' . $params['request_from_url'].'.jpg';
        if (file_exists($photoFilename)) {
            header("Content-Type: image/jpeg");
            header("Content-Length: " . filesize($photoFilename));
            echo file_get_contents($photoFilename);
        }

    }




}
