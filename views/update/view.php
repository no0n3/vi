<?php if (null === $update) : ?>
<div style="width: 100%;">
    <div style="margin: auto;    width: 400px;
    padding-top: 30px;">
        <h1>NO UPDATE FOUND</h1>
    </div>
</div>
<?php else : ?>
<script>
var commentCreator;

$(function() {
    var left = false, right = false;

    window.onkeydown = function(e) {
        var url = null;

        if (!left && 37 == e.keyCode) {
//            url = '/update/prev';
            if (<?= $prevUpdateId ? 'true' : 'false' ?>) {
                window.location = '<?= $categoryName ? "/$categoryName" : '' ?>/update/<?= $prevUpdateId ?>';
            }
            left = true;
        } else if (!right && 39 == e.keyCode) {
//            url = '/update/next';
            if (<?= $nextUpdateId ? 'true' : 'false' ?>) {
                window.location = '<?= $categoryName ? "/$categoryName" : '' ?>/update/<?= $nextUpdateId ?>';
            }
            right = true;
        } else {
            return;
        }

//        $.ajax({
//            url : url,
//            data : {
//                id : <?= $update['id'] ?>,
//                category : '<?= Vi::$app->request->get('category') ?>'
//            },
//            success : function(response) {
//                if (response) {
//                    window.location = '<?= Vi::$app->request->get('category') ? ('/' . Vi::$app->request->get('category')) : '' ?>/update/' + response;
//                }
//            }
//        });
    };

    var last = null;
    var hasLoadedBefore = false;
    var page = 0;
    var _firstToComment = <?= 0 == $update['comments'] ? 'true' : 'false' ?>;
    var _hasMore = true;

    var uc = document.getElementById('comments-cont');
    var moreComments = document.getElementById('load-more-comments');

    commentCreator = document.getElementById('create-comment');

    if (commentCreator) {
        commentCreator.onkeydown = function(e) {
            if (13 === e.keyCode) {
                e.stopPropagation();
                e.preventDefault();

                App.comment.createComment({
                    updateId : <?= Vi::$app->request->get('id') ?>,
                    content : commentCreator.value
                }, uc, function () {
                    if (_firstToComment) {
                        _firstToComment = false;

                        var firstToComment = document.getElementById('first-to-comment');

                        if (firstToComment) {
                            firstToComment.setAttribute('class', 'hidden');
                        }
                    }

                    commentCreator.value = '';
                }, function() {
                    return !_hasMore;
                });
            }
        };
    }

    function load() {
        App.comment.load({
            updateId : <?= Vi::$app->request->get('id') ?>,
            last : last,
            page : page
        }, uc, false, function(time, count, hasMore) {
            page++;

            _hasMore = hasMore;

            if (!hasMore) {
                moreComments.setAttribute('class', 'hidden');

                if (!hasLoadedBefore) {
                    var firstToComment = document.getElementById('first-to-comment');

                    if (firstToComment) {
                        firstToComment.setAttribute('class', '');
                    }
                }
            }

            hasLoadedBefore = true;
        });
    }

    moreComments.onclick = function() {
        load();
    };

    load();

    var buttons = document.getElementById('update-buttons');
    buttons.appendChild(App.button.vote({
        id : <?= $update['id'] ?>,
        upvotes : <?= $update['upvotes'] ?>,
        voted : '<?= $update['voted'] ?>',
        type : App.button.VOTE_TYPE_UPDATE
    }));

    var commentCont = document.createElement('span');
    var comment = document.createElement('span');
    var comments = document.createElement('span');
    comments.setAttribute('class', 'comments-count');

    comment.innerHTML = 'comments';
    comments.innerHTML = '<?= (int) $update['comments'] ?>';

    commentCont.appendChild(comment);
    commentCont.appendChild(comments);

    var sep = document.createElement('span');
    sep.innerHTML = ' - ';

    buttons.appendChild(sep);
    buttons.appendChild(commentCont);
 
});
</script>

<div style="width : 800px; margin : auto; position: relative; ">
    <h2 class='update-title'><?= $update['description'] ?>
    <?php foreach ($categories as $category) : ?>
        <a href="/<?= $category['name'] ?>" style="font-weight: initial;
    font-size: initial;
    vertical-align: middle;
    background-color: #3DAD3D;
color: white;
text-decoration: none;
    padding: 1px 5px;"><?= $category['name'] ?></a>
        <?php endforeach; ?>
    </h2>
    <div style="text-align: center;
    vertical-align: top;
    outline: 1px solid #ddd;">
        <img class="image" src="<?= $update['imageUrl'] ?>">
    </div>
    <div id='update-buttons'></div>
    <?php if (Vi::$app->user->isLogged()) : ?>
    <div>
        <textarea id="create-comment" placeholder="Write a comment..."></textarea>
    </div>
    <?php endif; ?>
    <div class='comments'>
        <div id="comments-cont"></div>
        <div>
            <span id="load-more-comments" class="load-more-comments">Show more comments</span>
        </div>
        <?php if (0 == $update['comments'] && Vi::$app->user->isLogged()) : ?>
        <div id="first-to-comment" class="hidden" style="text-align: center;">
            <span style="font-weight: bold;">
                Be the first to comment!
            </span>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
