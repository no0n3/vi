<?php
use Vi;

$categoryName = Vi::$app->request->get('category');
?>
<!DOCTYPE html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="/js/app.js"></script>
<link href="/css/app.css" rel="stylesheet" type="text/css">
<script>
function sAjax(ajaxData, hasCsrf) {
    hasCsrf = undefined === hasCsrf ? true : hasCsrf;

    if (hasCsrf) {
        ajaxData.data._csrf = '<?= isset($_SESSION['_csrf']) ? \components\Security::hash($_SESSION['_csrf']) : null ?>';
    }

    return $.ajax(ajaxData);
}

$(function() {
    App.user.csrfToken = '<?= /*\components\Security::hash($_SESSION['_csrf']null)*/null ?>';
    App.user.id = <?= Vi::$app->user->isLogged() ? Vi::$app->user->identity->id : 'null' ?>;

    var categoriesMenuButton = document.getElementById('categories');
    var categoriesMenu = document.getElementById('categories-menu');

    var userMenu = document.getElementById('user-menu');
    var userImg = document.getElementById('user-menu-toggle');

    categoriesMenuButton.onclick = function(e) {
        if ('hidden' === categoriesMenu.getAttribute('class')) {
            categoriesMenu.setAttribute('class', '');
        } else {
            categoriesMenu.setAttribute('class', 'hidden');
        }

        e.preventDefault();
    };

    if (userImg) {
        userImg.onclick = function(e) {
            if ('hidden' === userMenu.getAttribute('class')) {
                userMenu.setAttribute('class', '');
            } else {
                userMenu.setAttribute('class', 'hidden');
            }

            e.preventDefault();
        };
    }

    $('#logout-btn').on('click', function(e) {
        e.preventDefault();
        App.user.logout();
    });
});
</script>
</head>
<body>
<div class="header-cont">
    <div id="categories-menu" class="hidden">
        <ul class="category-list">
        <?php foreach ($this->categories as $category) : ?>
            <li class="category-item">
                <a href="/<?= $category['name'] ?>/fresh" class="category-item-link header-menu-link"><?= $category['name'] ?></a>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
    <ul class="ul1">
        <li class="li1 <?= (!$categoryName && Vi::$app->request->get('type') === \models\Update::TYPE_TRENDING) ? 'li1-selected' : '' ?>">
            <a href="/trending" class="category-type">Trending</a>
        </li>
        <li class="li1 <?= (!$categoryName && Vi::$app->request->get('type') === \models\Update::TYPE_FRESH) ? 'li1-selected' : '' ?>">
            <a href="/fresh" class="category-type">Fresh</a>
        </li>
        <li class="li1">
            <a id="categories" href="#" class="category-type">Categories</a>
        </li>
    </ul>
    <?php if (Vi::$app->user->isLogged()) : ?>
    <div class="header-user-area-cont">
        <div id="user-menu-toggle">
            <img id="user-img" class="header-user-img" src="<?= Vi::$app->user->identity->getProfilePicUrl() ?>" width="30px" height="30">
            <span class="header-username"><?= htmlspecialchars(Vi::$app->user->identity->username) ?></span>
        </div>
        <a href="/update/create" class="header-btn">submit</a>
    </div>

    <div id="user-menu" class="hidden">
        <ul class="category-list">
            <li class="category-item">
                <a href="/profile/<?= Vi::$app->user->identity->id ?>" class="header-user-menu-item header-menu-link">profile</a>
            </li>
            <li class="category-item">
                <a href="/settings/profile" class="header-user-menu-item header-menu-link">settings</a>
            </li>
            <li class="category-item">
                <a id="logout-btn" class="header-user-menu-item header-menu-link">logout</a>
            </li>
        </ul>
    </div>
    <?php else : ?>
    <div class="header-user-option-cont">
        <a href="/signup" class="header-btn"> sign up </a>
        <a href="/login" class="header-btn"> login </a>
    </div>
    <?php endif; ?>
</div>
<div class="page-container">

    <?= $view ?>

</div>
</body>
</html>
