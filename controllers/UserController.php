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
            if($userModel->load(Yii::$app->request->post())){
                $userModel->number = preg_replace("/[^0-9]/iu", '', $userModel->number);
                if($userModel->save()){
                    if($userProfModel->load(Yii::$app->request->post())){
                        $userProfModel->user_id = $userModel->id;
                        $userProfModel->save();
                    }
                    if($userModel->user_role) {
                        $getRole = Yii::$app->authManager->getRole($userModel->user_role);
                        Yii::$app->authManager->assign($getRole, $userModel->id);
                    }else{
                        $getRole = Yii::$app->authManager->getRole('user');
                        Yii::$app->authManager->assign($getRole, $userModel->id);
                    }
                    return 1;
                }else{
                    return 0;
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'delete-user'){
                $findUser = User::findOne(Yii::$app->request->post('id'));
                if(Yii::$app->db->createCommand()->update('user', ['is_active' => 0], [
                    'id' => $findUser->id,
                ])->execute()){
                    return 1;
                }else{
                    return 0;
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'search-user'){
                $findUser = User::find()->where(['like', 'name', '%' .Yii::$app->request->post('str') . '%', false])
                        ->orWhere(['like', 'surname', '%' .Yii::$app->request->post('str') . '%', false])
                        ->orWhere(['like', 'number', '%' .Yii::$app->request->post('str') . '%', false])
                        ->andWhere(['is_active' => 1])
                        ->with('role')->limit('8')->asArray()->all();
                
                return json_encode($findUser);
            }
            
        }
        $sort = [];
        if(\Yii::$app->request->get('act') == 'sort' && \Yii::$app->request->get('val') == 'asc'){
            $sort['act'] = 'sort';
            $sort['val'] = 'asc';
        }
        if(\Yii::$app->request->get('act') == 'sort' && \Yii::$app->request->get('val') == 'surname'){
            $sort['act'] = 'sort';
            $sort['val'] = 'surname';
        }
        if(\Yii::$app->request->get('act') == 'sortProf'){
            $sort['act'] = 'sortProf';
            $sort['val'] = \Yii::$app->request->get('val');
        }
        
        $getUsers = User::find()->where(['is_active' => 1]);
        $pages = new Pagination(['totalCount' => $getUsers->count(), 'pageSize' => 150]);
        $users = $getUsers->offset($pages->offset)
            ->limit($pages->limit)->with('role')->with('userProfession')
            ->all();
        
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
                }
            }
        }
        
        if(isset($sort['act']) && $sort['act'] == 'sort' && $sort['val'] == 'surname'){
            $users = \app\components\ScheduleComponent::sortFirstLetter($users, 'surname', false);
        }
        
        // Костыль на сортировку по профессии
        if(isset($sort['act']) && $sort['act'] == 'sortProf'){
            foreach ($users as $key => $value){
                if(+$value['userProfession']['prof']['id'] != +$sort['val']){
                    unset($users[$key]);
                }
            }
        }
        
        
        return $this->render('index', [
            'users' => $users,
            'userModel' => $userModel,
            'roleList' => $rolesList,
            'categories' => $categories,
            'profModel' => $userProfModel,
            'authAssignment' => $authAssignment,
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
                }
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
