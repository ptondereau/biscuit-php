<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\AuthorizerBuilder;
use Biscuit\Auth\Biscuit;
use Biscuit\Auth\FailedCheck;
use Biscuit\Auth\PublicKey;
use Biscuit\Exception\AuthorizationException;
use Biscuit\Exception\AuthorizerBuildException;
use Biscuit\Exception\BiscuitException;
use Biscuit\Exception\FormatException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SamplesTest extends TestCase
{
    private const UNSUPPORTED = [
        'test035_ffi.bc' => 'extern functions are not exposed to PHP',
    ];

    public static function validations(): iterable
    {
        foreach (self::samples()['testcases'] as $case) {
            foreach ($case['validations'] as $name => $validation) {
                yield trim("{$case['filename']} {$name}") => [$case['filename'], $validation];
            }
        }
    }

    public static function parsableTokens(): iterable
    {
        foreach (self::samples()['testcases'] as $case) {
            $validation = reset($case['validations']);
            if (!array_key_exists('Format', $validation['result']['Err'] ?? [])) {
                yield $case['filename'] => [$case, $validation['revocation_ids']];
            }
        }
    }

    #[Test]
    #[DataProvider('validations')]
    public function authorizationMatchesSpecification(string $filename, array $validation): void
    {
        $unsupported = self::UNSUPPORTED[$filename] ?? null;
        if ($unsupported !== null) {
            static::markTestSkipped($unsupported);
        }

        static::assertSame(
            self::expectedOutcome($validation['result']),
            self::outcome($filename, $validation['authorizer_code']),
        );
    }

    #[Test]
    #[DataProvider('parsableTokens')]
    public function tokenContentMatchesSpecification(array $case, array $revocationIds): void
    {
        $token = self::token($case['filename']);
        $indexes = range(0, $token->blockCount() - 1);

        static::assertSame(array_column($case['token'], 'code'), array_map($token->blockSource(...), $indexes));
        static::assertSame(
            array_column($case['token'], 'external_key'),
            array_map(static fn(int $index): ?string => $token->blockExternalKey($index)?->toHex(), $indexes),
        );
        static::assertSame($revocationIds, $token->revocationIds());
    }

    private static function expectedOutcome(array $result): array
    {
        $error = $result['Err'] ?? [];
        $logic = $error['FailedLogic'] ?? [];

        return match (true) {
            array_key_exists('Ok', $result) => ['Ok' => $result['Ok']],
            array_key_exists('Unauthorized', $logic) => ['Unauthorized' => $logic['Unauthorized']],
            array_key_exists('InvalidBlockRule', $logic) => ['Error' => AuthorizerBuildException::class],
            array_key_exists('Execution', $error) => ['Error' => AuthorizationException::class],
            array_key_exists('Format', $error) => ['Error' => FormatException::class],
        };
    }

    private static function outcome(string $filename, string $authorizerCode): array
    {
        try {
            $token = self::token($filename);
        } catch (FormatException) {
            return ['Error' => FormatException::class];
        }

        try {
            return [
                'Ok' => (new AuthorizerBuilder($authorizerCode))
                    ->build($token)
                    ->authorize()
                    ->getPolicyId(),
            ];
        } catch (AuthorizationException $e) {
            $policy = $e->getMatchedPolicy();
            if ($policy === null) {
                return ['Error' => AuthorizationException::class];
            }

            return [
                'Unauthorized' => [
                    'policy' => [ucfirst($policy->getKind()) => $policy->getPolicyId()],
                    'checks' => array_map(self::failedCheck(...), $e->getFailedChecks()),
                ],
            ];
        } catch (BiscuitException $e) {
            return ['Error' => $e::class];
        }
    }

    private static function failedCheck(FailedCheck $check): array
    {
        return match ($check->getOrigin()) {
            'block' => [
                'Block' => [
                    'block_id' => $check->getBlockId(),
                    'check_id' => $check->getCheckId(),
                    'rule' => $check->getRule(),
                ],
            ],
            'authorizer' => [
                'Authorizer' => [
                    'check_id' => $check->getCheckId(),
                    'rule' => $check->getRule(),
                ],
            ],
        };
    }

    private static function token(string $filename): Biscuit
    {
        return Biscuit::fromBytes(
            file_get_contents(self::samplesDir() . $filename),
            PublicKey::fromBytes(hex2bin(self::samples()['root_public_key'])),
        );
    }

    private static function samples(): array
    {
        static $samples;

        return $samples ??= json_decode(
            file_get_contents(self::samplesDir() . 'samples.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }

    private static function samplesDir(): string
    {
        static $dir;

        return $dir ??= dirname(self::cargoManifests()['biscuit-auth']) . '/samples/';
    }

    private static function cargoManifests(): array
    {
        $manifest = escapeshellarg(dirname(__DIR__) . '/Cargo.toml');
        $metadata = json_decode(
            (string) shell_exec("cargo metadata --format-version 1 --manifest-path {$manifest}"),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        return array_column($metadata['packages'], 'manifest_path', 'name');
    }
}
