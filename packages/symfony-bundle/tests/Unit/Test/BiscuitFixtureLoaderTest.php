<?php

declare(strict_types=1);

namespace Biscuit\BiscuitBundle\Tests\Unit\Test;

use Biscuit\BiscuitBundle\Test\BiscuitFixtureLoader;
use Biscuit\BiscuitBundle\Test\BiscuitFixtures;
use Biscuit\BiscuitBundle\Test\BiscuitTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(BiscuitFixtureLoader::class)]
#[CoversClass(BiscuitFixtures::class)]
final class BiscuitFixtureLoaderTest extends TestCase
{
    use BiscuitTestTrait;

    private string $fixtureDir;

    protected function setUp(): void
    {
        $this->fixtureDir = sys_get_temp_dir() . '/biscuit_fixtures_' . uniqid();
        mkdir($this->fixtureDir, 0o777, true);
    }

    protected function tearDown(): void
    {
        self::resetTestKeyPair();

        $files = glob($this->fixtureDir . '/*');
        if (false !== $files) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
        rmdir($this->fixtureDir);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itLoadsFixturesFromYamlFile(): void
    {
        $yamlContent = <<<YAML
            tokens:
              admin_token:
                code: |
                  user("admin");
                  role("admin");
              user_token:
                code: user("regular_user")
            YAML;

        $filePath = $this->fixtureDir . '/fixtures.yaml';
        file_put_contents($filePath, $yamlContent);

        $loader = new BiscuitFixtureLoader();
        $fixtures = $loader->load($filePath);

        self::assertInstanceOf(BiscuitFixtures::class, $fixtures);
        self::assertSame(2, $fixtures->count());
        self::assertTrue($fixtures->hasToken('admin_token'));
        self::assertTrue($fixtures->hasToken('user_token'));
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itLoadsFixturesFromArray(): void
    {
        $data = [
            'tokens' => [
                'test_token' => [
                    'code' => 'user("test")',
                ],
            ],
        ];

        $loader = new BiscuitFixtureLoader();
        $fixtures = $loader->loadFromArray($data);

        self::assertInstanceOf(BiscuitFixtures::class, $fixtures);
        self::assertTrue($fixtures->hasToken('test_token'));
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itLoadsTokenWithParams(): void
    {
        $data = [
            'tokens' => [
                'param_token' => [
                    'code' => 'user({user_id})',
                    'params' => ['user_id' => 'param_user'],
                ],
            ],
        ];

        $loader = new BiscuitFixtureLoader();
        $fixtures = $loader->loadFromArray($data);

        $token = $fixtures->getToken('param_token');
        $source = $token->blockSource(0);

        self::assertStringContainsString('user("param_user")', $source);
    }

    #[Test]
    public function itThrowsExceptionForNonExistentFile(): void
    {
        $loader = new BiscuitFixtureLoader();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fixture file not found');

        $loader->load('/non/existent/file.yaml');
    }

    #[Test]
    public function itThrowsExceptionForMissingTokensKey(): void
    {
        $data = ['other_key' => 'value'];

        $loader = new BiscuitFixtureLoader();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must contain a "tokens" key');

        $loader->loadFromArray($data);
    }

    #[Test]
    public function itThrowsExceptionForInvalidTokenDefinition(): void
    {
        $data = [
            'tokens' => [
                'invalid_token' => 'not_an_array',
            ],
        ];

        $loader = new BiscuitFixtureLoader();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must be an array');

        $loader->loadFromArray($data);
    }

    #[Test]
    #[RequiresPhpExtension('biscuit-php')]
    public function itUsesDefaultCodeWhenNotProvided(): void
    {
        $data = [
            'tokens' => [
                'default_token' => [],
            ],
        ];

        $loader = new BiscuitFixtureLoader();
        $fixtures = $loader->loadFromArray($data);

        $token = $fixtures->getToken('default_token');
        $source = $token->blockSource(0);

        self::assertStringContainsString('user("test")', $source);
    }
}
