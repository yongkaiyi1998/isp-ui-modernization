<style>
    .text-header {
        font-size: 1.2em;
        font-weight: bold;
    }
</style>

<div style="margin-top: 15px;">
    <fieldset class="category-border-main">
        <div class="category-border-main bg-success text-left" style="font-weight: bold; padding-left: 20px; font-size: 1.2em;">
            Record Information
        </div>
        <div class="row category-border-main">
            <div class="col-lg-3 text-header">
                Created By: <span style="color: grey;"><?php echo $created_by ?></span>
            </div>
            <div class="col-lg-3 text-header">
                Created At: <span style="color: grey;"><?php echo $created_at ?></span>
            </div>
            <div class="col-lg-3 text-header">
                Updated By: <span style="color: grey;"><?php echo $updated_by ?></span>
            </div>
            <div class="col-lg-3 text-header">
                Updated At: <span style="color: grey;"><?php echo $updated_at ?></span>
            </div>
    </fieldset>
</div>