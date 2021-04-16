<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Choose Your: Marvel`s Legends</title>
    <link rel="stylesheet" href="view/templates/style/main_style.css">
    <script defer src="view/templates/scripts/client.js"></script>
</head>

<body id="body">
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
        <div id='chooseEnemy'>
            <p>Choose Your Opponent</p>
            <select id="selector">

            </select>
            <p>
                <button onclick="invite()" value="Invite" id="inviteButton">Select</button>
            </p>

            <script>
                let battle_deck = <?php echo $_SESSION['battle_deck']; ?>;
                let login = <?php echo json_encode($_SESSION['login']) ?>;
            </script>

        </div>
    </main>
</body>

</html>