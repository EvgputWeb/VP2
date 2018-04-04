<?php

require_once 'Controller.php';
require_once APP . '/models/User.php';
require_once APP . '/models/Fileslist.php';

class FileslistController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Fileslist();
    }

    public function actionIndex()
    {
        $userInfo = User::getUserInfoByCookie();

        $viewData = [];
        $viewData['curSection'] = 'fileslist';

        if ($userInfo['isLogined']) {
            // Это авторизованный пользователь
            $viewData['login'] = $userInfo['login'];
            $viewData['name'] = $userInfo['name'];

            // Берём у модели список файлов
            $filesList = $this->model->getFilesList();

            if (is_array($filesList)) {
                $viewData['list'] = $filesList;
                $this->view->render('fileslist', $viewData);
            } else {
                $viewData['errorMessage'] = (string)$filesList;
                $this->view->render('error', $viewData);
            }
        } else {
            // Пользователь не авторизован - доступ в раздел запрещён
            $viewData['errorMessage'] = 'Отказано в доступе: необходимо авторизоваться';
            $this->view->render('error', $viewData);
        }
    }

    public function actionDeletePhoto(array $params)
    {
        if (!isset($params['id'])) {
            echo json_encode(['result' => 'fail', 'errorMessage' => 'Неверный запрос'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $userInfo = User::getUserInfoByCookie();
        if ($userInfo['isLogined']) {
            // Это авторизованный пользователь - он имеет права на удаление
            // Вызываем у модели функцию удаления
            $deletePhotoResult = $this->model->deletePhoto($params['id']);

            if ($deletePhotoResult === true) {
                echo json_encode(['result' => 'success'], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['result' => 'fail', 'errorMessage' => $deletePhotoResult], JSON_UNESCAPED_UNICODE);
            }
        } else {
            // Пользователь не авторизован - он не имеет прав
            echo json_encode(['result' => 'fail', 'errorMessage' => 'Вы не авторизованы. Нет прав на удаление'], JSON_UNESCAPED_UNICODE);
        }
    }
}
