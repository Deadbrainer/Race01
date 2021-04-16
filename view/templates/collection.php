<?php
foreach ($_SESSION['cards'] as $key => $value) {
    foreach ($value as $k => $v) {
        if ($k != 'img') {
            $file = fopen("view/templates/assets/Images/" . $key . ".png", 'c');
            fwrite($file, base64_decode($v));
            fclose($file);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Collection : Marvel`s Legends</title>
    <link rel="stylesheet" href="view/templates/style/main_style.css">
</head>

<body>
    <header class="header">
        <nav>
            <form action="index.php" method="post">
                <div class="nav">
                    <div class="logo">
                        <button type="submit" name="go_main"></button>
                    </div>
                    <div class="nav_but">
                        <input type="submit" name="play" value="Play">
                    </div>
                    <div class="nav_but">
                        <input type="submit" name="go_collection" value="Collection">
                    </div>
                    <div class="nav_but">
                        <?php if ($_SESSION['isLogin'] == true) : ?>
                            <?php if (file_exists("view/templates/assets/Images/Avatar.png")) : ?>
                                <div class="avatar">
                                    <button style="background-image: url(view/templates/assets/Images/<?=$_SESSION['login']?>.png)" type="submit" name="go_change_ava"></button>
                                </div>
                            <?php else : ?>
                                <div class="avatar_casual">
                                    <button type="submit" name="go_change_ava"></button>
                                </div>
                            <?php endif ?>
                            <input type="submit" name="logout" value="Log out">
                        <?php else : ?>
                            <input type="submit" name="go_log" value="Sing in">
                            <input type="submit" name="go_reg" value="Sing up">
                        <?php endif ?>
                    </div>
                </div>
            </form>
        </nav>
    </header>
    <main class="main_collection">
        <div class="collection">
            <?php if (isset($_POST['go_collection'])) : ?>
                <p>Legends Collection</p>
                <?php for ($i = 0; $i < 23; $i++) : ?>
                    <img src='view/templates/assets/Images/<?= $i ?>.png'>
                <?php endfor ?>
            <?php elseif (isset($_POST['deck']) || isset($_POST['cardDelete']) || isset($_POST['cardAdd'])) : ?>
                <?php if ($_SESSION['deck'] == 1) : ?>
                    <p>Your Deck 1</p>
                <?php elseif ($_SESSION['deck'] == 2) : ?>
                    <p>Your Deck 2</p>
                <?php elseif ($_SESSION['deck'] == 3) : ?>
                    <p>Your Deck 3</p>
                <?php endif ?>
                <form action="index.php" method="post">
                    <?php for ($i = 0; $i < 23; $i++) : ?>
                        <?php if (in_array($i, $_SESSION['chosen_deck'])) : ?>
                            <button type="submit" name="cardDelete" onclick="changeColor(this);" value="<?= $i ?>"><img src='view/templates/assets/Images/<?= $i ?>.png'><span class="delete_card">Delete</span></button>
                        <?php else : ?>
                            <button type="submit" class="notactive" name="cardAdd" onclick="changeColor(this);" value="<?= $i ?>"><img src='view/templates/assets/Images/<?= $i ?>.png'><span class="delete_card">Add</span></button>
                        <?php endif ?>
                    <?php endfor ?>
                </form>
            <?php endif ?>
        </div>
        <div class='decks'>
            <p>Your Decks</p>
            <form action="index.php" method="post">
                <div class="choose_decks">
                    <button type="submit" name="deck" value="1">Deck 1<br><?=$_SESSION['countdeck1']?>/10</button>
                    <button type="submit" name="deck" value="2">Deck 2<br><?=$_SESSION['countdeck2']?>/10</button>
                    <button type="submit" name="deck" value="3">Deck 3<br><?=$_SESSION['countdeck3']?>/10</button>
                </div>
            </form>
        </div>
    </main>
</body>

</html>