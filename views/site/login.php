<?php
use components\web\widgets\Form;
?>
<div class="start-page">
    <h2 class="header-title">Login</h2>
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

        $form->input('email', $user, 'email', [
            'attrs' => [
                'class' => 'form-inp'
            ],
        ])
            ->input('password', $user, 'password', [
                'attrs' => [
                    'class' => 'form-inp'
                ],
            ]);
        ?>
        <div class="submit-cont">
            <input type="submit" value="login" class="submit-btn">
        </div>
        <?= $form->endForm() ?>
</div>
