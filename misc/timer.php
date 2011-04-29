<?php
function timer() {
    static $lastime = 0;
    $r = $lastime ? time() + microtime() - $lastime : 0;
    $lastime = time() + microtime();
    return $r;
}




