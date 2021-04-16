const SERVER_ADDR = "10.11.6.9"; //"127.0.0.1"
const MAIN_SERVER_ADDR = "10.11.6.9";
const UID = 1; // TODO, change this from PHP
let timer_state = "none"
let localTurn = null;

Array.prototype.remove = function () {
    var what, a = arguments, L = a.length, ax;
    while (L && this.length) {
        what = a[--L];
        while ((ax = this.indexOf(what)) !== -1) {
            this.splice(ax, 1);
        }
    }
    return this;
};

var mainTimer = setInterval(function () {
    if (timer_state == "counting") {
        document.getElementById("timer_value").innerHTML = document.getElementById("timer_value").innerHTML * 1 - 1;
        if (document.getElementById("timer_value").innerHTML * 1 <= 0) {
            document.getElementById("timer_value").innerHTML = 30;
            timer_state == "waiting";

            if (localTurn) {
                endTurn();
            }
        }
    }
}, 1000);


//Net classes
class User {
    constructor(id, login) {
        this.id = id;
        this.login = login;
        this.state = "none";
    }
}

class Packet {
    constructor(type, user, data) {
        this.type = type;
        this.user = user;
        this.data = data;
    }
}


class Player {
    constructor(login, avatar, isLocal) {
        this.login = login;
        this.avatar = avatar;
        this.isLocal = isLocal;
        this.health = 15;
        this.stone = 1;
        this.max_stone = 1;
    }
    createHTML() {
        this.html = document.createElement('div')
        if (this.isLocal) {
            this.html.id = "player_info";
        } else {
            this.html.id = "enemy_info";
        }

        this.html.innerHTML = `<img src="` + this.avatar + `" alt="">
                                <p>` + this.login + `</p>
                                <p>`+ this.health + `❤️</p>
                                <p>`+ this.stone + "/" + this.max_stone + ` 💎</p>
                                <p>`+ playerDeck.length + ` in deck</p>`
    }
    update() {
        this.html.innerHTML = `<img src="` + this.avatar + `" alt="">
                                <p>` + this.login + `</p>
                                <p>`+ this.health + `❤️</p>
                                <p>`+ this.stone + "/" + this.max_stone + ` 💎</p>
                                <p>`+ this.deck + ` in deck</p>`
    }
}


class Card {
    constructor(id, name, attack, def, health, cost, img, PLAYER) {
        this.id = id;
        this.name = name;
        this.attack = attack;
        this.def = def;
        this.health = health;
        this.cost = cost;
        this.img = img;
        this.PLAYER = PLAYER;
    }

    createHTML() {
        let card = document.createElement('div');
        card.classList.add("card");
        card.onclick = () => { onCard(this); };

        let img = document.createElement('img');
        img.src = "http://" + MAIN_SERVER_ADDR + ":3000/" + this.img;
        // if (this.PLAYER) {
        //     img.src = "http://" + MAIN_SERVER_ADDR + "/" + this.img;
        // } else {
        //     img.src = "back_of_card.jpg"
        // }

        card.appendChild(img);

        // this.html_img = img;
        this.html = card;
    }

    createHealth() {
        let health = document.createElement('div');
        health.classList.add("health");
        health.innerText = this.health;
        this.html.appendChild(health);
        this.health_html = health;
    }

    moveToHand() {
        playerHand.push(this)
        this.createHTML();
        document.getElementById('hand').appendChild(this.html);
    }

    moveToBattlefield() {
        this.html.onclick = null;

        if (this.PLAYER) {
            document.getElementById('player_battlefield').appendChild(this.html);
            playerBattleField.push(this);
        } else {
            // this.html_img.src = img.src = "http://" + MAIN_SERVER_ADDR + "/" + this.img;
            document.getElementById('enemy_battlefield').appendChild(this.html);
            enemyBattleField.push(this);
        }

        playerHand.remove(this);

    }
}

function createDeck(data) {
    let j, x, i;
    let deck = [];
    for (let arr of data) {
        let card = new Card(arr[0], arr[1], arr[2], arr[3], arr[4], arr[5], arr[6], true);
        deck.push(card);
    }

    for (i = deck.length - 1; i > 0; i--) {
        j = Math.floor(Math.random() * (i + 1));
        x = deck[i];
        deck[i] = deck[j];
        deck[j] = x;
    }

    return deck;
}

let us = new User(UID, login);
let webSocket = new WebSocket("ws://" + SERVER_ADDR + ":2346");
let playerDeck = createDeck(battle_deck);
let playerHand = [];
let enemyHand = [];
let playerBattleField = [];
let enemyBattleField = [];
let player = null;
let enemy = null;

webSocket.onopen = function () {
    let packet = new Packet("init", us, null);
    webSocket.send(JSON.stringify(packet));
};

webSocket.onmessage = function (e) {
    let data = JSON.parse(e.data);
    switch (data['type']) {
        case 'auth':
            webSocket.send(JSON.stringify(new Packet("main_load", us, null)));
            break;
        case 'main_load': //load main elements of the game
            refreshUserlist(data.data);
            break;
        case 'invite_recieve':
            if (us.state == "none") {
                webSocket.send(JSON.stringify(new Packet("accept_invite", us, { 'target': data.data.login, 'source': us.login })));
            }
            break;
        case 'update_userlist':
            refreshUserlist(data.data);
            break;
        case 'start_battle':
            us.state = "battle";
            startBattle(data.data.enemy, data.data.first);
            break;
        case 'sync_in':

            document.getElementById('enemy_hand').innerHTML = "";
            document.getElementById('enemy_battlefield').innerHTML = "";
            document.getElementById('player_battlefield').innerHTML = "";
            for (let index = 0; index < data.data.in_hand; index++) {
                addEnemyCard();
            }

            enemyBattleField = [];
            for (const enemy_card of data.data.table) {
                let card = new Card(enemy_card.id, enemy_card.name, enemy_card.attack, enemy_card.def, enemy_card.health, enemy_card.cost, enemy_card.img, false)
                card.createHTML();
                card.createHealth();
                card.moveToBattlefield();
            }

            playerBattleField = [];
            for (const enemy_card of data.data.enemy_table) {
                let card = new Card(enemy_card.id, enemy_card.name, enemy_card.attack, enemy_card.def, enemy_card.health, enemy_card.cost, enemy_card.img, true)
                card.createHTML();
                card.createHealth();
                card.moveToBattlefield();
            }

            enemy.max_stone = data.data.enemy.max_stone;
            enemy.stone = data.data.enemy.stone;
            enemy.health = data.data.enemy.health;
            enemy.deck = data.data.enemy.deck;

            enemy.update();
            player.health = data.data.enemy_hp;
            player.update();
            if (player.health <= 0) {
                let notification = document.createElement('div');
                notification.id = "notification";
                notification.classList.add('center');
                notification.innerHTML = `<div class="center">
                                            You've loosed!
                                        </div>`
                document.getElementById('body').appendChild(notification);
                timer_state = "none";
                setTimeout(function () { location.replace("index.php"); }, 5000);
            }


            document.getElementById("timer_value").innerHTML = 30;
            timer_state == "counting";
            localTurn = !localTurn;
            if (localTurn) {
                enemy.html.classList.remove("turn");
                player.html.classList.add("turn");
            } else {
                player.html.classList.remove("turn");
                enemy.html.classList.add("turn");
            }
            break;
        default:
            break;
    }
};

function refreshUserlist(data) {
    if (document.getElementById("selector")) {
        document.getElementById("selector").remove();
    }

    let selectList = document.createElement("select");
    selectList.id = "selector";

    for (const element in data) {
        if (us.login == data[element].user.login) continue;
        let option = document.createElement("option");
        option.value = data[element].user.login;
        option.text = data[element].user.login;
        selectList.appendChild(option);
    }

    document.getElementById('chooseEnemy').appendChild(selectList);
}

function invite() {
    let packet = new Packet("invite", us, { 'login': document.getElementById("selector").value });
    webSocket.send(JSON.stringify(packet));
}

function startBattle(enemyLogin, first) {
    if (document.getElementById("chooseEnemy")) {
        document.getElementById("chooseEnemy").remove();
    }
    if (document.getElementsByClassName("header")[0]) {
        document.getElementsByClassName("header")[0].remove();
    }

    document.getElementsByTagName("body")[0].classList.add("battle");

    let game_container = document.createElement('div');
    game_container.id = "game_container"
    document.getElementById('body').appendChild(game_container);


    let battlefield = document.createElement('div');
    battlefield.id = "battlefield"
    game_container.appendChild(battlefield);

    let hands = document.createElement('div');
    hands.id = "hands"
    game_container.appendChild(hands);

    let battle_container = document.createElement('div');
    battle_container.id = "battle_container";
    battlefield.appendChild(battle_container)

    let enemy_battlefield = document.createElement('div');
    enemy_battlefield.id = "enemy_battlefield";
    battle_container.appendChild(enemy_battlefield);

    let player_battlefield = document.createElement('div');
    player_battlefield.id = "player_battlefield";
    battle_container.appendChild(player_battlefield);

    let hand = document.createElement('div');
    hand.id = "hand";
    let enemy_hand = document.createElement('div');
    enemy_hand.id = "enemy_hand";

    document.getElementById('hands').appendChild(hand);
    document.getElementById('hands').appendChild(enemy_hand);

    let timer = document.createElement('div');
    timer.id = "timer";
    let timer_value = document.createElement('span');
    timer_value.innerText = 30;
    timer_value.id = "timer_value";
    timer.appendChild(timer_value);

    let end_turn = document.createElement('button');
    end_turn.innerText = "End turn";
    end_turn.onclick = function () { endTurn(); };
    timer.appendChild(end_turn);
    document.getElementById("game_container").appendChild(timer);

    player = new Player(us.login, "http://" + MAIN_SERVER_ADDR + ":3000/view/templates/assets/Images/" + us.login + ".png", true);
    player.createHTML();
    document.getElementById("game_container").appendChild(player.html);
    enemy = new Player(enemyLogin, "http://" + MAIN_SERVER_ADDR + ":3000/view/templates/assets/Images/" + enemyLogin + ".png", false);
    enemy.createHTML()
    document.getElementById("game_container").appendChild(enemy.html);
    if (first == us.login) {
        let notification = document.createElement('div');
        notification.id = "notification";
        notification.classList.add('center');
        notification.innerHTML = `<div class="center">
                                    You are first!
                                </div>`
        document.getElementById('body').appendChild(notification);
        localTurn = true;
    } else {
        let notification = document.createElement('div');
        notification.id = "notification";
        notification.classList.add('center');
        notification.innerHTML = `<div class="center">
                                    ` + first + ` is first!
                                </div>`
        document.getElementById('body').appendChild(notification);
        localTurn = false;
    }


    setTimeout(function () {
        document.getElementById('notification').remove();
        playerDeck.pop().moveToHand()
        playerDeck.pop().moveToHand()
        playerDeck.pop().moveToHand()
        player.deck = playerDeck.length
        player.update();

        addEnemyCard();
        addEnemyCard();
        addEnemyCard();
        enemy.deck = 7;
        enemy.update();

        timer_state = "counting";
        if (localTurn) {
            enemy.html.classList.remove("turn");
            player.html.classList.add("turn");
        } else {
            player.html.classList.remove("turn");
            enemy.html.classList.add("turn");
        }
    }, 1000);
}

function endTurn() {
    if (localTurn) {
        document.getElementById("timer_value").innerHTML = 30;
        timer_state == "waiting";

        if (player.max_stone < 6) {
            player.max_stone++;
        }

        player.stone = player.max_stone;
        player.update();
        if (playerDeck.length > 0 && playerHand.length < 5) {
            player.deck = player.deck - 1;
            player.update();
            playerDeck.pop().moveToHand()
        }

        for (const card of playerBattleField) {
            if (enemyBattleField.length > 0) {
                let randomCard = enemyBattleField[Math.floor(Math.random() * enemyBattleField.length)];
                randomCard.health = randomCard.health - Math.max((card.attack - randomCard.def), 0);
                randomCard.health_html.innerHTML = randomCard.health;
                if (randomCard.health <= 0) {
                    randomCard.html.remove();
                    enemyBattleField.remove(randomCard);
                }
            } else {
                enemy.health -= card.attack;
            }
        }

        if (playerDeck.length == 0 && playerHand.length == 0) {
            player.health--;
        }

        enemy.update();
        webSocket.send(JSON.stringify(new Packet("sync_out", us, { 'target': enemy, 'in_hand': playerHand.length, 'table': playerBattleField, 'enemy': player, 'enemy_table': enemyBattleField, 'enemy_hp': enemy.health })));
        localTurn = !localTurn;

        if (enemy.health <= 0) {
            let notification = document.createElement('div');
            notification.id = "notification";
            notification.classList.add('center');
            notification.innerHTML = `<div class="center">
                                        You've won!
                                    </div>`
            document.getElementById('body').appendChild(notification);
            setTimeout(function () { location.replace("index.php"); }, 5000);
            timer_state = "none";
        }


        if (localTurn) {
            enemy.html.classList.remove("turn");
            player.html.classList.add("turn");
        } else {
            player.html.classList.remove("turn");
            enemy.html.classList.add("turn");
        }
    }

}

function onCard(card) {
    if (localTurn) {
        if (player.stone >= card.cost) {
            document.getElementById('hand').removeChild(card.html)
            card.createHealth();
            card.moveToBattlefield();
            player.stone -= card.cost;
            player.update();
        }
    }
}


function addEnemyCard() {
    let card = document.createElement('div');
    card.classList.add("card");
    let img = document.createElement('img');
    img.src = "http://" + MAIN_SERVER_ADDR + ":3000/view/templates/assets/Images/back_of_card.jpg";
    card.appendChild(img);
    document.getElementById('enemy_hand').appendChild(card);
}


// document.addEventListener("DOMContentLoaded", function (event) { //TODO remove for final
//     startBattle("test");
//     // addToBatlleField(1, true)
// });
