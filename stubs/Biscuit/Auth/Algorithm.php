<?php

declare(strict_types=1);

namespace Biscuit\Auth;

/**
 * Algorithm enum for cryptographic key operations.
 */
enum Algorithm: int
{
    case Ed25519 = 0;
    case Secp256r1 = 1;
}
