<?php
function remind($email, $password){
    mail($email, "ezuenko.ed@gmail.com", $password);
}
?>