<h2> Change password </h2>
<?php
use components\web\widgets\Form;

$model = new \models\forms\ChangePasswordForm();

        $form = Form::widget([
            'options' => [
                'template' => '
                    <div style="padding: 10px; background-color: white;">
                        <div>{label}</div>
                        <div style="margin-top: 5px;">{input}</div>
                        <div style="margin-top: 5px; color: red;">{error}</div>
                    </div>
                '
            ]
        ]);

        echo $form->input('password', $model, 'oldPassword', [
            'attrs' => [
                'style' => 'padding: 4px; border: 2px solid black;'
            ],
        ])
            ->input('password', $model, 'newPassword', [
                'attrs' => [
                    'style' => 'padding: 4px; border: 2px solid black;'
                ],
            ])
            ->input('password', $model, 'confirmPassword', [
                'attrs' => [
                    'style' => 'padding: 4px; border: 2px solid black;'
                ],
            ])
            ->submit('change password')
            ->endForm();
