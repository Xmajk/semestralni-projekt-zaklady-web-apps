<?php

/**
 * Generates a SHA-256 hash of the input string.
 *
 * This function acts as a wrapper for the native PHP hash function,
 * specifically using the SHA-256 algorithm to create a cryptographic
 * representation of the data.
 *
 * @param string $input The input string to be hashed.
 * @return string The calculated SHA-256 hash as a hexadecimal string.
 */
function hashSHA256(string $input): string {
    return hash('sha256', $input);
}