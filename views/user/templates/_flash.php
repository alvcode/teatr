<?php if( Yii::$app->session->hasFlash('success') ): ?>
    <div class="flash-message alert alert-success"><?php echo Yii::$app->session->getFlash('success'); ?></div>
<?php endif;?>
<?php if( Yii::$app->session->hasFlash('error') ): ?>
    <div class="flash-message alert alert-danger"><?php echo Yii::$app->session->getFlash('error'); ?></div>
<?php endif;?>