<?php
$modelName = $model->getClassName(false);
?>
<script>
$(function() {
    var $categories = document.getElementsByName('<?= $modelName ?>[categories][]');

    var $imgInp = $('#img-inp');
    var $title = $('#title');

    function getCheckedCategoriesCount() {
        var c = 0;

        for (var i in $categories) {
            if ($categories[i].checked) {
                c++;
            }
        }

        return c;
    }

    $('#f1').on('submit', function() {
        var status = true;

        if (0 >= getCheckedCategoriesCount()) {
            status = false;
        }
        if (0 >= $title.val().length) {
            status = false;
        }

        return status;
    });
});
</script>

<div style="width : 700px; margin : auto; position: relative; border : 0px solid gray; ">
    <div style="width : 250px; float: left;">
        <ul>
            <li <?= $settingType === 'profile' ? 'class="setting-selected"' : ''?>><a class="edit-opt" href="/settings/profile">Profile</a></li>
            <li <?= $settingType === 'picture' ? 'class="setting-selected"' : ''?>><a class="edit-opt" href="/settings/picture">Picture</a></li>
            <li <?= $settingType === 'password' ? 'class="setting-selected"' : ''?>><a class="edit-opt" href="/settings/password">Password</a></li>
        </ul>
    </div>
    <div style="margin : 10px; float: left; width : 400px">
        <?php if ($success) : ?>
        <div class="success">
            <span>Profile successfully updated.</span>
        </div>
        <?php else : ?>
        <?php
        if ('password' === $settingType) {
            echo $this->render('_changePasswordForm');
        } else if ('picture' === $settingType) {
            echo $this->render('_changePorilePicture', [
                'modelName' => $modelName
            ]);
        } else {
            echo $this->render('_generalSettings', [
                'categories' => $categories,
                'model' => $model,
                'modelName' => $modelName,
            ]);
        } ?>
        <?php endif; ?>
    </div>
</div>
