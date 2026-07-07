<?php

declare(strict_types=1);

namespace Biscuit\Auth;

/**
 * Signature algorithm used by Biscuit keys.
 *
 * Every factory method accepting a nullable Algorithm defaults to
 * {@see Algorithm::Ed25519} when null is given.
 */
enum Algorithm: int
{
    case Ed25519 = 0;
    case Secp256r1 = 1;
}
