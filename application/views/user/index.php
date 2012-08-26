<?php foreach ($user as $user_item): ?>

    <h2><?php echo $user_item['username'] ?></h2>
    <div id="main">
        <?php echo $user_item['name'] ?>
        <?php echo $user_item['email'] ?>
    </div>

<?php endforeach ?>