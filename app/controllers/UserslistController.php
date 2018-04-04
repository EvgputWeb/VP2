<?php

require_once 'Controller.php';
require_once APP . '/models/User.php';
require_once APP . '/models/Userslist.php';


class UserslistController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Userslist();
    }


    public function actionIndex()
    {
        $userInfo = User::getUserInfoByCookie();

        $viewData = [];
        $viewData['curSection'] = 'userslist';

        if ($userInfo['isLogined']) {
            // Это авторизованный пользователь
            $viewData['login'] = $userInfo['login'];
            $viewData['name'] = $userInfo['name'];

            // Берём у модели список пользователей
            $usersList = $this->model->getUsersList();

            if (is_array($usersList)) {
                $viewData['list'] = $usersList;
                $this->view->render('userslist', $viewData);
            } else {
                $viewData['errorMessage'] = (string)$usersList;
                $this->view->render('error', $viewData);
            }
        } else {
            // Пользователь не авторизован - доступ в раздел запрещён
            $viewData['errorMessage'] = 'Отказано в доступе: необходимо авторизоваться';
            $this->view->render('error', $viewData);
        }
    }


    public function actionDeleteUser(array $params)
    {
        if (!isset($params['id'])) {
            echo json_encode(['result' => 'fail', 'errorMessage' => 'Неверный запрос'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $userInfo = User::getUserInfoByCookie();
        if ($userInfo['isLogined']) {
            // Это авторизованный пользователь - он имеет права на удаление

            if ($userInfo['id'] == $params['id']) {
                // Хочет удалить сам себя
                echo json_encode(['result' => 'fail', 'errorMessage' => 'Нельзя удалять себя'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Запоминаем информацию об удаляемом, чтобы потом отправить её в скрипт
            $info = User::getUserInfoById($params['id']);
            if (empty($info)) {
                // Нет такого пользователя
                echo json_encode(['result' => 'fail', 'errorMessage' => 'Нет такого пользователя'], JSON_UNESCAPED_UNICODE);
                return;
            }
            // Вызываем у модели функцию удаления
            $deleteUserResult = $this->model->deleteUser($params['id']);

            if ($deleteUserResult === true) {
                echo json_encode(['result' => 'success', 'name' => $info['name'], 'login' => $info['login']], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['result' => 'fail', 'errorMessage' => $deleteUserResult], JSON_UNESCAPED_UNICODE);
            }
        } else {
            // Пользователь не авторизован - он не имеет прав
            echo json_encode(['result' => 'fail', 'errorMessage' => 'Вы не авторизованы. Нет прав на удаление'], JSON_UNESCAPED_UNICODE);
        }
    }


    public function actionEditUser(array $params)
    {
        $viewData = [];
        $viewData['curSection'] = 'userslist';

        if (isset($params['submit'])) {
            // Была нажата кнопка "Сохранить" в форме редактирования пользователя
            // Сохраняем данные и делаем редирект на список пользователей
            $userId = $params['request_from_url'];
            // Если есть фотка, то добавляем в данные пользователя имя загруженного файла
            if ((isset($_FILES)) && (isset($_FILES['photo']))) {
                $params['photo_filename'] = $_FILES['photo']['tmp_name'];
            }
            // Обращаемся к модели для обновления данных пользователя
            $res = $this->model->updateUserData($userId, $params);
            if ($res === true) {
                header('Location: /userslist');
            } else {
                $viewData['errorMessage'] = (string)$res;
                $this->view->render('error', $viewData);
            }
        } else {
            // Просто отображаем форму редактирования
            $userInfo = User::getUserInfoByCookie();
            if ($userInfo['isLogined']) {
                // Это авторизованный пользователь
                $viewData['login'] = $userInfo['login'];
                $viewData['name'] = $userInfo['name'];

                $userId = $params['request_from_url'];
                // Берём у модели данные пользователя
                $viewData['user'] = $this->model->getUserData($userId);
                // Отображаем их в форме редактирования
                $this->view->render('edituser', $viewData);
            } else {
                // Пользователь не авторизован - доступ в раздел запрещён
                $viewData['errorMessage'] = 'Отказано в доступе: необходимо авторизоваться';
                $this->view->render('error', $viewData);
            }
        }
    }


    public function actionNewUser(array $params)
    {
        $viewData = [];
        $viewData['curSection'] = 'userslist';

        if (isset($params['submit'])) {
            // Была нажата кнопка "Сохранить" в форме создания пользователя
            // Сохраняем данные и делаем редирект на список пользователей

            // Если есть фотка, то добавляем в данные пользователя имя загруженного файла
            if ((isset($_FILES)) && (isset($_FILES['photo']))) {
                $params['photo_filename'] = $_FILES['photo']['tmp_name'];
            }

            // Обращаемся к модели для создания нового пользователя
            $res = $this->model->createNewUser($params);
            if ($res === true) {
                header('Location: /userslist');
            } else {
                $viewData['errorMessage'] = (string)$res;
                $this->view->render('error', $viewData);
            }
        } else {
            // Просто отображаем форму создания пользователя
            $userInfo = User::getUserInfoByCookie();
            if ($userInfo['isLogined']) {
                // Это авторизованный пользователь
                $viewData['login'] = $userInfo['login'];
                $viewData['name'] = $userInfo['name'];

                // Отображаем форму создания нового пользователя
                $this->view->render('newuser', $viewData);
            } else {
                // Пользователь не авторизован - доступ в раздел запрещён
                $viewData['errorMessage'] = 'Отказано в доступе: необходимо авторизоваться';
                $this->view->render('error', $viewData);
            }
        }
    }
}
