<h2>Change Profile Picture</h2>
<form method="post" enctype="multipart/form-data">
    <div class="field-sep">
        <div>
            <span> Profile Picture: </span>
        </div>
        <div>
            <input type="file" name="<?= $modelName ?>[image]" accept="image/*">
        </div>
    </div>
    <input type="hidden" name="_csrf" value="<?= components\Security::hash($_SESSION['_csrf']) ?>">
    <input type="submit" value="chance picture">
</form>
