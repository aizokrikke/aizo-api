<?php
function ArrayToJSON($in) {
    $out = '[';
    $start = true;
    foreach($in as $item) {
        if (!$start) {
            $out .= ', ';
        }
        $out .= json_encode($item);
        $start = false;
    }
    $out .= ']';

    return $out;
}
