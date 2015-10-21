<?php
use components\web\widgets\Form;
?>
<div class="start-page">
    <?php if ($success) : ?>
    <div class="success">
        <span>You have successfully registered!</span>
        <a href="/site/login">login</a>
    </div>
    <?php else : ?>
    <h2 class="header-title">Sign up</h2>
    <div class="start-page-cont">
    <?php
            $form = Form::widget([
                'options' => [
                    'template' => '
                        <div style="padding: 20px; padding-bottom: 0px; background-color: ;">
                            <div>{label}</div>
                            <div style="margin-top: 5px;">{input}</div>
                            <div style="margin-top: 5px; color: red;">{error}</div>
                        </div>
                    '
                ]
            ]);

             $form->input('email', $model, 'email', [
                'attrs' => [
                    'class' => 'form-inp'
                ],
            ])
                ->input('text', $model, 'username', [
                'attrs' => [
                    'class' => 'form-inp'
                ],
            ])
                ->input('password', $model, 'password', [
                    'attrs' => [
                        'class' => 'form-inp'
                    ],
                ]);
            ?>
            <div class="submit-cont">
                <input type="submit" value="sign up" class="submit-btn">
            </div>
            <?= $form->endForm() ?>
    </div>
    <?php endif; ?>
</div>
