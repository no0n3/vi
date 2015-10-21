<?php
use components\helpers\ArrayHelper;

$type = Vi::$app->request->get('type');
?>

<script>
$(function() {
    App.update.category = '<?= Vi::$app->request->get('category') ?>';

    var loadedBefore = false;
    var page = 0;

    var uc = document.getElementById('updates-cont');
    var noResultFound = document.getElementById('no-results-found');
    var loadingDiv = document.getElementById('loading');

    var loading = false;

    var alreadyLoaded = {};

    function load() {
        if (loading) {
            return;
        }

        loadingDiv.setAttribute('class', '');

        loading = true;

        $.ajax({
            type:'get',
            url : "/update/load",
            data : {
                category : '<?= Vi::$app->request->get('category') ?>',
                page : page,
                type : '<?= \models\Update::isValidType(Vi::$app->request->get('type')) ? Vi::$app->request->get('type') : null ?>'
            },
            success : function(response) {
                for (var i = 0; i < response.length; i++) {
                    if (alreadyLoaded[response[i].id]) {
                        alreadyLoaded[response[i].id] = true;
                        continue;
                    }

                    uc.appendChild(
                        App.update.renderUpdate(response[i])
                    );
                }

                if (0 < response.length) {
                    loading = false;
                } else if (!loadedBefore) {
                    noResultFound.setAttribute('class', '');
                }

                page++;
                loadedBefore = true;
            }
        }).always(function() {
            loadingDiv.setAttribute('class', 'hidden');
        });
    }

    load();

    $(window).scroll(function () {
        if ($(window).scrollTop() + $(window).height() >=
            $(document).height() - $(document).height() / 15
        ) {
            load();
        }
    });
});
</script>
<?php if (!empty($category) && in_array($category, ArrayHelper::getKeyArray($this->categories, 'name'))) : ?>
    <div style="width:100%; border-bottom : 1px solid #ddd;">
        <div class="<?= 0 < count($mostPopular) ? 'page' : 'page-no-popular' ?>" style="margin : auto;">
            <h2 class="category-section-title">
                <?= $category ?>
            </h2>
            <div>
                <a  class="updates-type <?= \models\Update::TYPE_TRENDING === $type ? 'updates-type-selected' : '' ?>" href="/<?= $category ?>/trending">trending</a>
                <a class="updates-type <?= (\models\Update::TYPE_FRESH === $type || !in_array($type, [
                    \models\Update::TYPE_FRESH, \models\Update::TYPE_TRENDING
                ])) ? 'updates-type-selected' : '' ?>" href="/<?= $category ?>/fresh">fresh</a>
            </div>
        </div>
    </div>
<?php endif; ?>
<div class="<?= 0 < count($mostPopular) ? 'page' : 'page-no-popular' ?>">
    <div style="width : 500px; float: left;">
        <div id="updates-cont">
            <div id="no-results-found" class="hidden">
                <h3>No updates found.</h3>
            </div>
        </div>
        <div id="loading">
            <img src="/images/loading.gif">
        </div>
    </div>
    <?php if (0 < count($mostPopular)) : ?>
    <div style="border : 0px solid black; margin-left: 30px; width : 300px; float: left;">
        <h3 style="margin: 10px; padding : 0px; text-align: center;">Most popular</h3>
        <?php foreach ($mostPopular as $update) : ?>
        <div style="padding : 5px;">
            <div>
                <a href="<?= $update['updateUrl'] ?>">
                    <img class="image" src="/images/updates/<?= $update['id'] ?>/250xX.jpeg" style="width : 100%;">
                </a>
            </div>
            <div style="font-weight : bold;">
                <h3 class="update-title-list">
                    <a href="/update/35" class="update-link"><?= $update['description'] ?></a>
                </h3>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
