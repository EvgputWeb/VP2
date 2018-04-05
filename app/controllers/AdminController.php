<?php

require_once 'Controller.php';
require_once APP . '/models/User.php';


class AdminController extends Controller
{
    public function actionIndex()
    {
        $userInfo = User::getUserInfoByCookie();

        if (!$userInfo['authorized']) {
            // Надо авторизоваться
            header('Location: /user/auth');
            return;
        }

        // Авторизованный пользователь
        $viewData['login'] = $userInfo['login'];
        $this->view->render('admin', $viewData);
    }
}
