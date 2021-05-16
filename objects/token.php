<?php

class token {
    public function generate() {
        return bin2hex(openssl_random_pseudo_bytes(64));
    }

}
