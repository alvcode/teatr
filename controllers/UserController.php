<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\User;
use yii\data\Pagination;
use app\models\AuthItem;
use app\models\AuthItemChild;

class UserController extends AccessController
{
    
    public $layout = 'board';
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(){
        $userModel = new User();
        
        if(Yii::$app->request->isAjax){
            if($userModel->load(Yii::$app->request->post())){
                $userModel->number = preg_replace("/[^0-9]/iu", '', $userModel->number);
                if($userModel->save()){
                    if($userModel->user_role) {
                        $getRole = Yii::$app->authManager->getRole($userModel->user_role);
                        Yii::$app->authManager->assign($getRole, $userModel->id);
                    }
                    return 1;
                }else{
                    return 0;
                }
            }
        }
        
        $getUsers = User::find();
        $pages = new Pagination(['totalCount' => $getUsers->count(), 'pageSize' => 30]);
        $users = $getUsers->offset($pages->offset)
            ->limit($pages->limit)->with('role')
            ->all();
        
        $authAssignment = new \app\models\AuthAssignment();
        $rolesList = AuthItem::find()->where(['type' => 1])->asArray()->all();
        
        return $this->render('index', [
            'users' => $users,
            'userModel' => $userModel,
            'roleList' => $rolesList,
            'authAssignment' => $authAssignment,
        ]);
    }
    
    public function actionUserSingle($id){
        $getUser = User::find()->where(['id' => $id])->with('role')->asArray()->one();
        
        return $this->render('user-single', [
            'user' => $getUser,
        ]);
    }
    
    
    /**
     * Управление ролями и разрешениями
     */
    public function actionRbac()
    {
//        $this->layout = 'main';
        
        if(Yii::$app->request->isAjax){
            if (Yii::$app->user->isGuest){
                return false;
            }else{
                if(Yii::$app->request->post('trigger') == 'add-role'){
                    $createRole = Yii::$app->authManager->createRole(Yii::$app->request->post('name'));
                    $createRole->description = Yii::$app->request->post('description');
                    Yii::$app->authManager->add($createRole);

                    return 1;
                }
                if(Yii::$app->request->post('trigger') == 'add-perm'){
                    $createPerm = Yii::$app->authManager->createPermission(Yii::$app->request->post('name'));
                    $createPerm->description = Yii::$app->request->post('description');
                    Yii::$app->authManager->add($createPerm);
                    
                    return 1;
                }
                if(Yii::$app->request->post('trigger') == 'assign-perm'){
                    $getRole = Yii::$app->authManager->getRole(Yii::$app->request->post('nameRole'));
                    $getPerms = Yii::$app->authManager->getPermissionsByRole(Yii::$app->request->post('nameRole'));
                    foreach ($getPerms as $key=>$value){
                        Yii::$app->authManager->removeChild($getRole, $value);
                    }
                    $permArray = Yii::$app->request->post('permList');
                    foreach ($permArray as $key=>$value){
                        $permObject = Yii::$app->authManager->getPermission($value);
                        Yii::$app->authManager->addChild($getRole, $permObject);
                    }
                    return 1;
                }
                if(Yii::$app->request->post('trigger') == 'assign-role'){
                    $getRoleParent = Yii::$app->authManager->getRole(Yii::$app->request->post('nameRole'));
                    $getChildRoles = Yii::$app->authManager->getChildRoles(Yii::$app->request->post('nameRole'));
                    foreach ($getChildRoles as $key=>$value){
                        Yii::$app->authManager->removeChild($getRoleParent, $value);
                    }
                    $childRolesArray = Yii::$app->request->post('roleList');
                    foreach ($childRolesArray as $key=>$value){
                        $roleObject = Yii::$app->authManager->getRole($value);
                        Yii::$app->authManager->addChild($getRoleParent, $roleObject);
                    }
                    return 1;
                }
                if(Yii::$app->request->post('trigger') == 'delete-child'){
                    $getRoleParent = Yii::$app->authManager->getRole(Yii::$app->request->post('nameParent'));
                    
                    if(Yii::$app->request->post('typeChild') == '1'){
                        $getChild = Yii::$app->authManager->getRole(Yii::$app->request->post('nameChild'));
                    }elseif (Yii::$app->request->post('typeChild') == '2') {
                        $getChild = Yii::$app->authManager->getPermission(Yii::$app->request->post('nameChild'));
                    }
                    Yii::$app->authManager->removeChild($getRoleParent, $getChild);
                    return 1;
                }
                if(Yii::$app->request->post('trigger') == 'delete-perm'){
                    $getPerm = Yii::$app->authManager->getPermission(Yii::$app->request->post('name'));
                    Yii::$app->authManager->remove($getPerm);
                    return 1;
                }
                if(Yii::$app->request->post('trigger') == 'delete-role'){
                    $getRole = Yii::$app->authManager->getRole(Yii::$app->request->post('name'));
                    Yii::$app->authManager->remove($getRole);
                    return 1;
                }
            }
        }
        
        $roles = AuthItem::find()->where(['type' => 1])->with('children')->asArray()->all();
        $perm = AuthItem::find()->where(['type' => 2])->asArray()->all();
        
        return $this->render('rbac', [
            'roles' => $roles,
            'perm' => $perm,
        ]);
        
    }

}
