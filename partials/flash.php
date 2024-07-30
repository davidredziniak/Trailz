<?php
/*put this at the bottom of the page so any templates
 populate the flash variable and then display at the proper timing*/
?>
<div class="container" id="flash">
    <?php $messages = getMessages(); ?>
    <?php if ($messages) : ?>
        <?php foreach ($messages as $msg) : ?>
            <div class="row justify-content-center">
                <div class="alert alert-<?php se($msg, 'color', 'info'); ?> mt-4 fade show" role="alert">
                <?php se($msg, "text", ""); ?>
                <a href="#" class="close" data-bs-dismiss="alert" aria-label="close" style="text-decoration: none;float: right;">&times;</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<script>
    //used to pretend the flash messages are below the header
    function moveMeUp(ele) {
        let target = document.getElementsByTagName("header")[0];
        if (target) {
            target.after(ele);
        }
    }

    moveMeUp(document.getElementById("flash"));
</script>