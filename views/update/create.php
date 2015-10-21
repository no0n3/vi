<?php
use components\web\widgets\Form;

$modelName = $model->getClassName(false);
?>
<script>

var $categoriesError;
var categories;

var $title;
var $titleError;

var $imgInp;
var $imageError;

function getCheckedCategoriesCount() {
    var c = 0;

    for (var i in categories) {
        if (categories[i].checked) {
            c++;
        }
    }

    return c;
}

$(function() {
    categories = document.getElementsByName('<?= $modelName ?>[categories][]');

    $imgInp = $('#image');
    $imageError = $('#image-error');

    $title = $('#title');
    $titleError = $('#title-error');

    $categoriesError = $('#categories-error');
    
    $title.on('change', function() {
        if (0 >= $title.val().length) {
            $titleError.html('Title cannot be empty.');
        } else {
            $titleError.html('');
        }
    });

    $('#f1').on('submit', function() {
        var status = true;

        if (0 >= getCheckedCategoriesCount()) {
            $categoriesError.html('You must choose at least one category.');
            status = false;
        }
        if (0 >= $title.val().length) {
            status = false;
            $titleError.html('Title cannot be empty.');
        }
        if (0 >= $imgInp.val().length) {
            $imageError.html('You must choose an image.');
        }

        return status;
    });
});

function checkboxChecked(checkbox) {
    var $checkbox = $(checkbox);

    if (checkbox.checked) {
        $categoriesError.html('');
    } else if (0 >= getCheckedCategoriesCount()) {
        $categoriesError.html('You must choose at least one category.');
    }
}

</script>
<div style="width : 400px; margin : auto; position: relative; padding-top: 15px;">
    <?php if ($success) : ?>
    <div class="success">
        <span> Update successfully created </span>
        <a href="/update/<?= $model->newUpdateId ?>">view update</a>
    </div>
    <?php else : ?>
    <h1> Create Update </h1>
    <form action="/update/create" id="f1" method="post" enctype="multipart/form-data">
        <div class="field-sep">
            <div>
                <span> Title: </span>
            </div>
            <div>
                <textarea class="textarea" id="title" name="<?= $modelName ?>[title]" value="<?= $model->title ?>"></textarea>
            </div>
            <span id="title-error" class="error"></span>
        </div>
        <div class="field-sep">
            <div>
                <span> Image: </span>
            </div>
            <div>
                <input id="image" type="file" name="<?= $modelName ?>[image]" value="<?= $model->title ?>" accept="image/jpeg, image/png">
            </div>
            <span id="image-error" class="error"></span>
        </div>
        <div class="field-sep">
            <div>
                <span> Choose categories: </span>
            </div>
            <div>
                <?php foreach ($categories as $category) : ?>
                <span class="category-cont category-cbx">
                    <input class="category-opt" onchange="checkboxChecked(this)"id="img-inp" type="checkbox" name="<?= $modelName ?>[categories][]" value="<?= $category['id'] ?>">
                    <span><?= $category['name'] ?></span>
                </span>
                <?php endforeach; ?>
            </div>
            <span id="categories-error" class="error"></span>
        </div>
        <input type="hidden" name="_csrf" value="<?= components\Security::hash($_SESSION['_csrf']) ?>">
        <input type="submit" value="post update">
    </form>

    <?php endif; ?>
</div>
