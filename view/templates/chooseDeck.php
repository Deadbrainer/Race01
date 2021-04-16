<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Choose Deck: Marvel`s Legends</title>
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
    <main>
    <div class='choosedecks'>
            <p>Choose Your Decks For Play</p>
            <form action="index.php" method="post">
                <div class="choose_deck">
                    <button type="submit" name="deckforbattle" value="1">Deck 1<br><?=$_SESSION['countdeck1']?>/10</button>
                    <button type="submit" name="deckforbattle" value="2">Deck 2<br><?=$_SESSION['countdeck2']?>/10</button>
                    <button type="submit" name="deckforbattle" value="3">Deck 3<br><?=$_SESSION['countdeck3']?>/10</button>
                </div>
            </form>
        </div>
    </main>
</body>

</html>