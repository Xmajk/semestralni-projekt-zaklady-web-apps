<?php
function hashSHA256(string $input): string {
    return hash('sha256', $input);
}

