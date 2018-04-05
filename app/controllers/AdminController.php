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
            }
            $this->viewData['files'] = File::getFilesListOf($this->userInfo['id']);
            $this->view->render('admin_files', $this->viewData);
        }
    }


}
