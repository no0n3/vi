<h2> Edit Profile </h2>
<form action="/index.php/user/settings?t=profile" id="f1" method="post">
    <div class="field-sep">
        <div>
            <span> Username: </span>
        </div>
        <div>
            <input id="username" class="int-field" type="text" name="<?= $modelName ?>[username]" value="<?= $model->username ?>">
        </div>
    </div>
    <div class="field-sep">
        <div>
            <span> Description: </span>
        </div>
        <div>
            <textarea id="image" class="textarea" name="<?= $modelName ?>[description]" value="<?= $model->description ?>"></textarea>
        </div>
    </div>
    <div class="field-sep">
        <div>
            <span> Choose categories: </span>
        </div>
        <div>
            <?php foreach ($categories as $category) { ?>
            <span class="category-cont category-cbx">
                <input <?= in_array($category['id'], $model->userCategories) ? 'checked="true"' : '' ?> class="category-opt" type="checkbox" name="<?= $modelName ?>[categories][]" value="<?= $category['id'] ?>">
                <span><?= $category['name'] ?></span>
            </span>
            <?php } ?>
        </div>
    </div>
    <input type="hidden" name="_csrf" value="<?= components\Security::hash($_SESSION['_csrf']) ?>">
    <input type="submit" value="post update">
</form>
