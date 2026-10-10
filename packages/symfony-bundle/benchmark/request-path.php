<?php

declare(strict_types=1);

/*
 * Benchmark of the per-request hot path: header extraction, token parse
 * (signature verification), revocation check against a static list (100 ids)
 * and an in-memory store (1000 entries), then one voter authorization with
 * an authorizer fact template.
 *
 * Usage:
 *   php -d extension=<libbiscuit_php.so> benchmark/request-path.php [iterations]
 *
 * Profile with Blackfire (reduce iterations 10-40x):
 *   blackfire run php -d extension=<blackfire.so> -d extension=<libbiscuit_php.so> \
 *       benchmark/request-path.php 100
 *
 * Warm up once and discard the first run. The output must stay
 * ACCESS_GRANTED on every iteration; the script aborts if it diverges.
 */

use Biscuit\Auth\KeyPair;
use Biscuit\BiscuitBundle\Authorizer\AuthorizerBuilderFactory;
use Biscuit\BiscuitBundle\Key\KeyManager;
use Biscuit\BiscuitBundle\Policy\PolicyRegistry;
use Biscuit\BiscuitBundle\Revocation\RevocationChecker;
use Biscuit\BiscuitBundle\Revocation\RevocationEntry;
use Biscuit\BiscuitBundle\Revocation\Store\ArrayRevocationStore;
use Biscuit\BiscuitBundle\Revocation\Store\StaticRevocationStore;
use Biscuit\BiscuitBundle\Security\Authenticator\BiscuitAuthenticator;
use Biscuit\BiscuitBundle\Security\User\BiscuitUser;
use Biscuit\BiscuitBundle\Security\Voter\BiscuitVoter;
use Biscuit\BiscuitBundle\Token\BiscuitTokenManager;
use Biscuit\BiscuitBundle\Token\Extractor\HeaderTokenExtractor;
use Biscuit\BiscuitBundle\Token\Template\Applier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

require __DIR__ . '/../vendor/autoload.php';

if (!extension_loaded('biscuit_php')) {
    fwrite(STDERR, "The biscuit_php extension is required: php -d extension=<libbiscuit_php.so> ...\n");
    exit(1);
}

$iterations = (int) ($argv[1] ?? 2000);

$keyPair = new KeyPair();
$keyManager = new KeyManager($keyPair->getPublicKey()->toHex(), $keyPair->getPrivateKey()->toHex(), null, null);
$tokenManager = new BiscuitTokenManager($keyManager);

$builder = $tokenManager->createBuilder('user("alice"); role("admin"); check if time($t), $t < 2033-01-01T00:00:00Z');
$biscuit = $tokenManager->build($builder);
$biscuit = $tokenManager->attenuate($biscuit, $tokenManager->createBlockBuilder('check if operation($r, "read")'));
$serialized = $tokenManager->serialize($biscuit);

$staticIds = [];
for ($i = 0; $i < 100; ++$i) {
    $staticIds[] = str_pad(dechex($i), 64, 'a');
}
$entries = [];
for ($i = 0; $i < 1000; ++$i) {
    $entries[] = new RevocationEntry(str_pad(dechex($i), 64, 'b'));
}
$checker = new RevocationChecker(
    ['static' => new StaticRevocationStore($staticIds), 'in_memory' => new ArrayRevocationStore($entries)],
    'allow',
);

$authenticator = new BiscuitAuthenticator(new HeaderTokenExtractor(), $tokenManager, $checker);

$registry = new PolicyRegistry(['can_read' => 'allow if user($u)']);
$voter = new BiscuitVoter($registry, null, new AuthorizerBuilderFactory(new Applier(), [
    'can_read' => ['facts' => ['operation({resource}, "read")']],
]));

$request = Request::create('/api/resource', 'GET', server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $serialized]);

$run = static function () use ($authenticator, $voter, $request): int {
    $passport = $authenticator->authenticate($request);
    $user = $passport->getUser();
    assert($user instanceof BiscuitUser);

    return $voter->vote(new UsernamePasswordToken($user, 'api'), ['resource' => 'doc-1'], ['can_read']);
};

$expected = $run();
if (1 !== $expected) {
    fwrite(STDERR, "unexpected vote result: {$expected}\n");
    exit(1);
}

$start = hrtime(true);
for ($i = 0; $i < $iterations; ++$i) {
    if ($run() !== $expected) {
        fwrite(STDERR, "output diverged at iteration {$i}\n");
        exit(1);
    }
}
$elapsedMs = (hrtime(true) - $start) / 1_000_000;

printf(
    "iterations=%d total=%.1fms per_request=%.3fms peak_mem=%.1fMB\n",
    $iterations,
    $elapsedMs,
    $elapsedMs / $iterations,
    memory_get_peak_usage(true) / 1048576,
);
