<?php if (null === $model) : ?>
<div style="width: 100%;">
    <div style="margin: auto;    width: 300px;
    padding-top: 30px;">
        <h1>NO USER FOUND</h1>
    </div>
</div>
<?php else : ?>
<script>

$(function() {
    var last = null;
    var uc = document.getElementById('updates-cont');
    var userInfoArea = document.getElementById('user-info-area');
    var userQuickInfoArea = document.getElementById('user-quick-info-area');
    var loadingDiv = document.getElementById('loading');

    var loadedBefore = false;

    var noResultFound = document.getElementById('no-results-found');

    var loading = false;

    function load() {
        if (loading) {
            return;
        }

        loadingDiv.setAttribute('class', '');

        loading = true;

        $.ajax({
            type:'get',
            url : "/update/userUpdates",
            data : {
                userId : <?= Vi::$app->request->get('id') ?>,
                last : last
            },
            success : function(response) {
                for (var i = 0; i < response.length; i++) {
                    uc.appendChild(
                        App.update.renderUpdate(response[i])
                    );
                }

                if (0 < response.length) {
                    last = response[response.length - 1].created_at;
                    loading = false;
                } else if (!loadedBefore) {
                    noResultFound.setAttribute('class', '');
                }

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
<div id="user-info-area" style="background-color: #4B4F4A; color : white; width: 100%; padding: 20px 0px;">
    <div style="text-align: center;">
        <img style="border-radius: 200px;" src="<?= models\User::getProfilePictureUrl($model['has_profile_pic'], $model['id']) ?>" width="150" height="150">
    </div>
    <div style="text-align: center;">
        <?= $model['username'] ?>
    </div>
    <div style="text-align: center;
    max-width: 400px;
    margin: auto; margin-top: 10px;">
        <?= $model['description'] ?>
    </div>
</div>

<div class="<?= 0 < count($mostPopular) ? 'page' : 'page-no-popular' ?>">
    <div style="width : 500px; float: left;">
        <div id="updates-cont" style="border : 0px solid black; width : 500px; float: left;">
            <div id="updates-cont" style="border : 0px solid black; width : 500px; float: left;">
                <div id="no-results-found" class="hidden">
                    <h3>No recent activity.</h3>
                </div>
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
                    <a href="/update/<?= $update['id'] ?>" class="update-link"><?= $update['description'] ?></a>
                </h3>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
