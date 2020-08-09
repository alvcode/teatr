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
use app\models\ProffCategories;
use app\models\Profession;
use app\models\UserProfession;
use app\models\EventType;
use app\models\TimesheetConfig;

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
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['visible_users'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['user-single'],
                        'roles' => ['crud_all_users'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['rbac'],
                        'roles' => ['visible_rbac_setting'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['profession'],
                        'roles' => ['visible_profession'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['login-as'],
                        'roles' => ['login_as'],
                    ],
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
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(){
        $userModel = new User();
        $userProfModel = new UserProfession();
        
        if(Yii::$app->request->isAjax){
            if(Yii::$app->request->post('trigger') == 'new-user'){
                $number = preg_replace("/[^0-9]/iu", '', Yii::$app->request->post('number'));
                if(!TimesheetConfig::checkRepeat(Yii::$app->request->post('timesheet'))){
                    return json_encode(['result' => 'error', 'data' => 'В настройке для табелей имеются повторяющиеся типы мероприятий']);
                }
//                $findByNumber = User::find()->where(['number' => $number])->all();
//                if($findByNumber){
//                    return json_encode(['result' => 'error', 'data' => 'Пользователь с таким номером телефона уже есть в программе']);
//                }
                $findByEmail = User::find()->where(['email' => Yii::$app->request->post('email'), 'is_active' => 1])->all();
                if($findByEmail){
                    return json_encode(['result' => 'error', 'data' => 'Пользователь с таким E-mail уже есть в программе']);
                }
                $userModel->name = Yii::$app->request->post('name');
                $userModel->surname = Yii::$app->request->post('surname');
                $userModel->email = trim(Yii::$app->request->post('email'));
                $userModel->number = $number;
                $userModel->show_full_name = Yii::$app->request->post('showFullName');
                $userModel->password = Yii::$app->request->post('password');
                $userModel->user_role = Yii::$app->request->post('userRole');
                
                if($userModel->save()){
                    $userProfModel->user_id = $userModel->id;
                    $userProfModel->prof_id = Yii::$app->request->post('profId');
                    $userProfModel->save();
                    
                    TimesheetConfig::setConfig(Yii::$app->request->post('timesheet'), $userModel->id);
                    
                    if($userModel->user_role) {
                        $getRole = Yii::$app->authManager->getRole($userModel->user_role);
                        Yii::$app->authManager->assign($getRole, $userModel->id);
                    }else{
                        $getRole = Yii::$app->authManager->getRole('user');
                        Yii::$app->authManager->assign($getRole, $userModel->id);
                    }
                    return json_encode(['result' => 'ok']);
                }else{
                    return json_encode(['result' => 'error', 'data' => 'Что-то пошло не так. Проверьте правильность заполнения полей']);
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'delete-user'){
                $findUser = User::findOne(Yii::$app->request->post('id'));
                if(Yii::$app->user->can('crud_all_users')){
                    if(Yii::$app->db->createCommand()->update('user', ['is_active' => 0], [
                        'id' => $findUser->id,
                    ])->execute()){
                        return 1;
                    }else{
                        return 0;
                    }
                }else{
                    return 0;
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'search-user'){
                $findUser = User::find()->where(['like', 'name', '%' .Yii::$app->request->post('str') . '%', false])
                        ->orWhere(['like', 'surname', '%' .Yii::$app->request->post('str') . '%', false])
                        ->orWhere(['like', 'number', '%' .Yii::$app->request->post('str') . '%', false])
                        ->andWhere(['is_active' => 1])
                        ->with('role')->with('userProfession')->limit('8')->asArray()->all();
                
                return json_encode($findUser);
            }
            
            if(Yii::$app->request->post('trigger') == 'get-timesheet'){
                $getTimesheet = TimesheetConfig::find()->where(['user_id' => Yii::$app->request->post('userId')])->with('eventType')->asArray()->all();
                return json_encode(['result' => 'ok', 'data' => $getTimesheet]);
            }
            
            if(Yii::$app->request->post('trigger') == 'set-timesheet'){
                if(!TimesheetConfig::checkRepeat(Yii::$app->request->post('timesheet'))){
                    return json_encode(['result' => 'error', 'data' => 'В настройке для табелей имеются повторяющиеся типы мероприятий']);
                }
                TimesheetConfig::setConfig(Yii::$app->request->post('timesheet'), Yii::$app->request->post('userId'));
                return json_encode(['result' => 'ok']);
            }
            
        }
        $sort = [];
//        if(\Yii::$app->request->get('act') == 'sort' && \Yii::$app->request->get('val') == 'asc'){
//            $sort['act'] = 'sort';
//            $sort['val'] = 'asc';
//        }
//        if(\Yii::$app->request->get('act') == 'sort' && \Yii::$app->request->get('val') == 'surname'){
//            $sort['act'] = 'sort';
//            $sort['val'] = 'surname';
//        }
        if(\Yii::$app->request->get('act') == 'sortProf'){
            $sort['act'] = 'sortProf';
            $sort['val'] = \Yii::$app->request->get('val');
        }
        
        $users = User::find()->where(['is_active' => 1])->andWhere(['!=', 'email', 'alvcode@ya.ru'])->with('role')->with('userProfession')->all();
        $users = \app\components\ScheduleComponent::sortFirstLetter($users, 'surname', false);
        
        $authAssignment = new \app\models\AuthAssignment();
        $rolesList = AuthItem::find()->where(['type' => 1])->asArray()->all();
        
        $categoryAll = ProffCategories::find()->with('professions')->asArray()->all();
        $categories = [];
        foreach($categoryAll as $key => $value){
            if($value['professions']){
                foreach($value['professions'] as $keyP => $valueP){
                    $countArr = count($categories);
                    $categories[$countArr]['id'] = $valueP['id'];
                    $categories[$countArr]['name'] = $valueP['name'] ." (".$value['name'] .")";
                    $categories[$countArr]['prof_cat'] = $value['id'];
                }
            }
        }
        
        // Костыль на сортировку по профессии. Хотя не такой уж и костыль :)
        if(isset($sort['act']) && $sort['act'] == 'sortProf'){
            foreach ($users as $key => $value){
                if((int)$value['userProfession']['prof']['id'] != (int)$sort['val']){
                    unset($users[$key]);
                }
            }
        }
        // Делаем фильтр для служб (отображаем только своих сотрудников) +
        // на создание отображаем только необходимые данные
        if(!Yii::$app->user->can('crud_all_users') && Yii::$app->user->can('crud_profcat_users')){
            $getProfCat = UserProfession::find()->select('profession.proff_cat_id')->where(['user_id' => Yii::$app->user->identity->id])
                    ->leftJoin('profession', 'profession.id = user_profession.prof_id')->asArray()->one();
            $thisUserProfCat = $getProfCat["proff_cat_id"];
            foreach ($users as $key => $value){
                if((int)$value['userProfession']['prof']["proff_cat_id"] != (int)$thisUserProfCat){
                    unset($users[$key]);
                }
            }
            foreach ($categories as $key => $value){
                if((int)$thisUserProfCat != (int)$value["prof_cat"]){
                    unset($categories[$key]);
                }
            }
//            echo "<pre>";
//            var_dump($categories); exit();
        }
        // Фильтруем список ролей в соответствии с правами
        foreach ($rolesList as $key => $value){
            if(!Yii::$app->user->can('create_priv_' .$value['name'])){
                unset($rolesList[$key]);
            }
        }
        
        $eventsType = EventType::find()->where(['is_active' => 1])->asArray()->all();
        
        return $this->render('index', [
            'users' => $users,
            'userModel' => $userModel,
            'roleList' => $rolesList,
            'categories' => $categories,
            'profModel' => $userProfModel,
            'authAssignment' => $authAssignment,
            'eventType' => $eventsType,
            'sort' => $sort,
        ]);
    }
    
    public function actionUserSingle($id){
        $getUser = User::find()->where(['id' => $id])->with('role')->one();
        $rolesList = AuthItem::find()->where(['type' => 1])->asArray()->all();
        $userProfession = UserProfession::find()->where(['user_id' => $id])->one();
        if(!$userProfession){
            $userProfession = new UserProfession();
        }
        
        
        if ($getUser->load(Yii::$app->request->post())) {
            $checkUser = true;
//            $findByNumber = User::find()->where(['number' => preg_replace("/[^0-9]/iu", '', $getUser->number)])
//                    ->andWhere(['!=', 'id', $getUser->id])->all();
//            if($findByNumber){
//                Yii::$app->session->setFlash('error', "Пользователь с таким номером телефона уже существует в программе");
//                $checkUser = false;
//            }
            $findByEmail = User::find()->where(['email' => $getUser->email, 'is_active' => 1])
                    ->andWhere(['!=', 'id', $getUser->id])->all();
            if($findByEmail){
                Yii::$app->session->setFlash('error', "Пользователь с таким E-mail уже есть в программе");
                $checkUser = false;
            }
            if($checkUser){
                $getUser->number = preg_replace("/[^0-9]/iu", '', $getUser->number);

                if($getUser->save()){
                    if($userProfession->load(Yii::$app->request->post())){
                        $userProfession->user_id = $getUser->id;
                        $userProfession->save();
                    }
                    if($getUser->user_role) {
                        $getRolesOld = Yii::$app->authManager->getRolesByUser($getUser->id);
                        foreach($getRolesOld as $key=>$value){
                            Yii::$app->authManager->revoke($value, $getUser->id);
                        }
                        $getRole = Yii::$app->authManager->getRole($getUser->user_role);
                        Yii::$app->authManager->assign($getRole, $getUser->id);
                    }

                    Yii::$app->session->setFlash('success', "Данные успешно изменены");
                }else{
                    Yii::$app->session->setFlash('error', "Что-то пошло не так. Обратитесь в поддержку");
                }
            }
        }

        
        $userRole = Yii::$app->authManager->getRolesByUser($getUser->id);
        $userRole = array_shift($userRole);
        
        $categoryAll = ProffCategories::find()->with('professions')->asArray()->all();
        $categories = [];
        foreach($categoryAll as $key => $value){
            if($value['professions']){
                foreach($value['professions'] as $keyP => $valueP){
                    $countArr = count($categories);
                    $categories[$countArr]['id'] = $valueP['id'];
                    $categories[$countArr]['name'] = $valueP['name'] ." (".$value['name'] .")";
                    $categories[$countArr]['prof_cat'] = $value['id'];
                }
            }
        }
        
        // Делаем фильтр для служб (отображаем только своих сотрудников) +
        // на создание отображаем только необходимые данные
        if(!Yii::$app->user->can('crud_all_users') && Yii::$app->user->can('crud_profcat_users')){
            $getProfCat = UserProfession::find()->select('profession.proff_cat_id')->where(['user_id' => Yii::$app->user->identity->id])
                    ->leftJoin('profession', 'profession.id = user_profession.prof_id')->asArray()->one();
            $thisUserProfCat = $getProfCat["proff_cat_id"];
            foreach ($categories as $key => $value){
                if((int)$thisUserProfCat != (int)$value["prof_cat"]){
                    unset($categories[$key]);
                }
            }
//            echo "<pre>";
//            var_dump($categories); exit();
        }
        
        foreach ($rolesList as $key => $value){
            if(!Yii::$app->user->can('create_priv_' .$value['name'])){
                unset($rolesList[$key]);
            }
        }
        
        return $this->render('user-single', [
            'user' => $getUser,
            'roleUser' => $userRole,
            'roleList' => $rolesList,
            'categories' => $categories,
            'profModel' => $userProfession,
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
    
    /**
     * Управление профессиями
     */
    public function actionProfession()
    {
        $categoryModel = new ProffCategories();
        $professionModel = new Profession();
        
        $categoryAll = ProffCategories::find()->with('professions')->asArray()->all();
        
        if(Yii::$app->request->isAjax){
            if(Yii::$app->request->post('trigger') == 'add-profession'){
                $aProffModel = new Profession();
                $aProffModel->name = Yii::$app->request->post('professionName');
                $aProffModel->proff_cat_id = Yii::$app->request->post('catId');
                if($aProffModel->validate() && $aProffModel->save()){
                    return json_encode($aProffModel->id);
                }else{
                    return 0;
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'rename-profession'){
                $proffModel = Profession::findOne(Yii::$app->request->post('profId'));
                $proffModel->name = Yii::$app->request->post('professionName');
                if($proffModel->save()){
                    return 1;
                }else{
                    return 0;
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'remove-profession'){
                $searchUserProf = UserProfession::find()->where(['prof_id' => Yii::$app->request->post('profId')])->all();
                if($searchUserProf){
                    return 2;
                }else{
                    Yii::$app->db->createCommand()->delete('profession', ['id' => Yii::$app->request->post('profId')])->execute();
                    return 1;
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'rename-category'){
                $catModel = ProffCategories::findOne(Yii::$app->request->post('catId'));
                $catModel->name = Yii::$app->request->post('categoryName');
                $catModel->alias = Yii::$app->request->post('categoryAlias');
                if($catModel->save()){
                    return 1;
                }else{
                    return 0;
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'delete-category'){
                $getProfessions = Profession::find()->where(['proff_cat_id' => Yii::$app->request->post('catId')])->asArray()->all();
                if($getProfessions){
                    $profIds = \yii\helpers\ArrayHelper::getColumn($getProfessions, 'id');
                    $searchUserProf = UserProfession::find()->where(['prof_id' => $profIds])->all();
                    if($searchUserProf){
                        return 2;
                    }else{
                        Yii::$app->db->createCommand()->delete('proff_categories', ['id' => Yii::$app->request->post('catId')])->execute();
                        return 1;
                    }
                }else{
                    Yii::$app->db->createCommand()->delete('proff_categories', ['id' => Yii::$app->request->post('catId')])->execute();
                    return 1;
                }
            }
        }
        
        
        if ($categoryModel->load(Yii::$app->request->post())) {
            if($categoryModel->save()){
                Yii::$app->session->setFlash('success', "Категория успешно добавлена");
            }else{
                Yii::$app->session->setFlash('error', "Что-то пошло не так, обратитесь к разработчику");
            }
            return $this->redirect('/user/profession/');
        }else{
            
        }
        
        return $this->render('profession', [
            'addCategory' => $categoryModel,
            'addProfession' => $professionModel,
            'prof' => $categoryAll,
        ]);
    }
    
    /**
     * Логин под выбранным пользователем
     */
    public function actionLoginAs($id)
    {
        if(!Yii::$app->user->isGuest){
            Yii::$app->user->logout();
        }
        $getUser = User::findOne(['id' => $id]);
        if($getUser->loginAs()){
            return Yii::$app->getResponse()->redirect('/panel')->send();
        }

    }

}
