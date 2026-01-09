<?php

declare(strict_types=1);

namespace Biscuit\Tests;

use Biscuit\Auth\Authorizer;
use Biscuit\Auth\AuthorizerBuilder;
use Biscuit\Auth\Biscuit;
use Biscuit\Auth\BiscuitBuilder;
use Biscuit\Auth\BlockBuilder;
use Biscuit\Auth\Check;
use Biscuit\Auth\Fact;
use Biscuit\Auth\KeyPair;
use Biscuit\Auth\Policy;
use Biscuit\Auth\PrivateKey;
use Biscuit\Auth\PublicKey;
use Biscuit\Auth\Rule;
use Biscuit\Auth\ThirdPartyBlock;
use Biscuit\Auth\ThirdPartyRequest;
use Biscuit\Auth\UnverifiedBiscuit;
use PHPUnit\Framework\TestCase;

class BiscuitTest extends TestCase
{
    public function testKeyPairGeneration(): void
    {
        $kp = new KeyPair();
        static::assertInstanceOf(KeyPair::class, $kp);

        $publicKey = $kp->getPublicKey();
        static::assertInstanceOf(PublicKey::class, $publicKey);

        $privateKey = $kp->getPrivateKey();
        static::assertInstanceOf(PrivateKey::class, $privateKey);
    }

    public function testKeyPairFromPrivateKey(): void
    {
        $privateKeyHex = 'ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97';
        $privateKey = new PrivateKey($privateKeyHex);

        $kp = KeyPair::fromPrivateKey($privateKey);
        static::assertInstanceOf(KeyPair::class, $kp);

        static::assertSame($privateKeyHex, $kp->getPrivateKey()->toHex());
    }

    public function testPublicKeyFromHex(): void
    {
        $publicKeyHex = 'ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189';
        $publicKey = new PublicKey($publicKeyHex);

        static::assertSame($publicKeyHex, $publicKey->toHex());
    }

    public function testPublicKeyFromBytes(): void
    {
        $publicKeyHex = 'ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189';
        $publicKey = new PublicKey($publicKeyHex);

        $bytes = $publicKey->toBytes();
        $publicKey2 = PublicKey::fromBytes(pack('C*', ...$bytes));

        static::assertSame($publicKey->toHex(), $publicKey2->toHex());
    }

    public function testPrivateKeyFromHex(): void
    {
        $privateKeyHex = 'ed25519-private/12aca40167fbdd1a11037e9fd440e3d510d9d9dea70a6646aa4aaf84d718d75a';
        $privateKey = new PrivateKey($privateKeyHex);

        static::assertSame($privateKeyHex, $privateKey->toHex());
    }

    public function testPrivateKeyFromBytes(): void
    {
        $privateKeyHex = 'ed25519-private/12aca40167fbdd1a11037e9fd440e3d510d9d9dea70a6646aa4aaf84d718d75a';
        $privateKey = new PrivateKey($privateKeyHex);

        $bytes = $privateKey->toBytes();
        $privateKey2 = PrivateKey::fromBytes(pack('C*', ...$bytes));

        static::assertSame($privateKey->toHex(), $privateKey2->toHex());
    }

    public function testBiscuitBuilder(): void
    {
        $kp = new KeyPair();
        $builder = new BiscuitBuilder();

        $builder->addCode('user("alice")');
        $builder->addFact(new Fact('resource("file1")'));
        $builder->addRule(new Rule('can_read($user, $res) <- user($user), resource($res)'));
        $builder->addCheck(new Check('check if user($u)'));

        $biscuit = $builder->build($kp->getPrivateKey());
        static::assertInstanceOf(Biscuit::class, $biscuit);
    }

    public function testBiscuitBuilderWithParameters(): void
    {
        $kp = new KeyPair();
        $builder = new BiscuitBuilder();

        $builder->addCode('user({username}); resource({res});', ['username' => 'alice', 'res' => 'file1']);

        $biscuit = $builder->build($kp->getPrivateKey());
        static::assertInstanceOf(Biscuit::class, $biscuit);
    }

    public function testBiscuitBuilderAddCodeWithOptionalParams(): void
    {
        $kp = new KeyPair();
        $builder = new BiscuitBuilder();

        // Without params (existing behavior)
        $builder->addCode('user("alice")');

        // With params (new unified API)
        $builder->addCode('resource({res})', ['res' => 'file1']);

        $biscuit = $builder->build($kp->getPrivateKey());
        static::assertInstanceOf(Biscuit::class, $biscuit);
        static::assertStringContainsString('user("alice")', $biscuit->blockSource(0));
        static::assertStringContainsString('resource("file1")', $biscuit->blockSource(0));
    }

    public function testBiscuitBuilderConstructorWithCode(): void
    {
        $kp = new KeyPair();

        // Constructor with code and params
        $builder = new BiscuitBuilder('user({id})', ['id' => 'alice']);
        $biscuit = $builder->build($kp->getPrivateKey());

        static::assertInstanceOf(Biscuit::class, $biscuit);
        static::assertStringContainsString('user("alice")', $biscuit->blockSource(0));
    }

    public function testBiscuitBuilderConstructorEmpty(): void
    {
        $builder = new BiscuitBuilder();
        static::assertInstanceOf(BiscuitBuilder::class, $builder);
    }

    public function testBlockBuilderConstructorWithCode(): void
    {
        $builder = new BlockBuilder('resource({res})', ['res' => 'file1']);
        static::assertStringContainsString('resource("file1")', (string) $builder);
    }

    public function testAuthorizerBuilderConstructorWithCode(): void
    {
        $authBuilder = new AuthorizerBuilder('allow if true');
        $authorizer = $authBuilder->buildUnauthenticated();
        $policy = $authorizer->authorize();
        static::assertSame(0, $policy);
    }

    public function testBiscuitSerialization(): void
    {
        $privateKeyHex = 'ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97';
        $privateKey = new PrivateKey($privateKeyHex);
        $kp = KeyPair::fromPrivateKey($privateKey);

        $builder = new BiscuitBuilder();
        $builder->addCode('user("alice")');

        $biscuit = $builder->build($privateKey);

        $base64 = $biscuit->toBase64();
        static::assertIsString($base64);

        $parsed = Biscuit::fromBase64($base64, $kp->getPublicKey());
        static::assertInstanceOf(Biscuit::class, $parsed);

        $bytes = $biscuit->toBytes();
        static::assertIsArray($bytes);

        $parsed2 = Biscuit::fromBytes(pack('C*', ...$bytes), $kp->getPublicKey());
        static::assertInstanceOf(Biscuit::class, $parsed2);
    }

    public function testBiscuitAppend(): void
    {
        $kp = new KeyPair();
        $builder = new BiscuitBuilder();
        $builder->addCode('user("alice")');

        $biscuit = $builder->build($kp->getPrivateKey());
        static::assertSame(1, $biscuit->blockCount());

        $block = new BlockBuilder();
        $block->addCode('resource("file1")');

        $biscuit2 = $biscuit->append($block);
        static::assertSame(2, $biscuit2->blockCount());
    }

    public function testBlockBuilder(): void
    {
        $builder = new BlockBuilder();
        $builder->addCode('resource("file1")');
        $builder->addFact(new Fact('fact(true)'));
        $builder->addRule(new Rule('head($v) <- fact($v)'));
        $builder->addCheck(new Check('check if fact(true)'));

        static::assertInstanceOf(BlockBuilder::class, $builder);
    }

    public function testBlockBuilderWithParameters(): void
    {
        $builder = new BlockBuilder();
        $builder->addCode('resource({res}); permission({perm});', ['res' => 'file1', 'perm' => 'read']);

        static::assertInstanceOf(BlockBuilder::class, $builder);
    }

    public function testAuthorizerBuilder(): void
    {
        $kp = new KeyPair();
        $biscuitBuilder = new BiscuitBuilder();
        $biscuitBuilder->addCode('user("alice")');
        $biscuit = $biscuitBuilder->build($kp->getPrivateKey());

        $authBuilder = new AuthorizerBuilder();
        $authBuilder->addCode('allow if user("alice")');
        $authBuilder->addFact(new Fact('resource("file1")'));
        $authBuilder->addRule(new Rule('can_read($u, $r) <- user($u), resource($r)'));
        $authBuilder->addCheck(new Check('check if user($u)'));
        $authBuilder->addPolicy(new Policy('allow if user("alice")'));

        $authorizer = $authBuilder->build($biscuit);
        static::assertInstanceOf(Authorizer::class, $authorizer);
    }

    public function testAuthorizerBuilderWithParameters(): void
    {
        $kp = new KeyPair();
        $biscuitBuilder = new BiscuitBuilder();
        $biscuitBuilder->addCode('user("alice")');
        $biscuit = $biscuitBuilder->build($kp->getPrivateKey());

        $authBuilder = new AuthorizerBuilder();
        $authBuilder->addCode('allow if user({username})', ['username' => 'alice']);

        $authorizer = $authBuilder->build($biscuit);
        static::assertInstanceOf(Authorizer::class, $authorizer);
    }

    public function testAuthorizerBuilderUnauthenticated(): void
    {
        $authBuilder = new AuthorizerBuilder();
        $authBuilder->addCode('fact("test"); allow if fact("test")');

        $authorizer = $authBuilder->buildUnauthenticated();
        static::assertInstanceOf(Authorizer::class, $authorizer);

        $policy = $authorizer->authorize();
        static::assertSame(0, $policy);
    }

    public function testCompleteLifecycle(): void
    {
        $privateKeyHex = 'ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97';
        $privateKey = new PrivateKey($privateKeyHex);
        $kp = KeyPair::fromPrivateKey($privateKey);

        $biscuitBuilder = new BiscuitBuilder();
        $biscuitBuilder->addCode('user({id})', ['id' => '1234']);

        foreach (['read', 'write'] as $right) {
            $biscuitBuilder->addFact(new Fact("right(\"{$right}\")"));
        }

        $biscuit = $biscuitBuilder->build($privateKey);

        $block = new BlockBuilder();
        $block->addCode('check if user($u)');
        $biscuit = $biscuit->append($block);

        $token = $biscuit->toBase64();
        $parsedToken = Biscuit::fromBase64($token, $kp->getPublicKey());

        $authBuilder = new AuthorizerBuilder();
        $authBuilder->addCode('allow if user({id})', ['id' => '1234']);
        $authorizer = $authBuilder->build($parsedToken);

        $policy = $authorizer->authorize();
        static::assertSame(0, $policy);
    }

    public function testAuthorizerQuery(): void
    {
        $kp = new KeyPair();

        $biscuitBuilder = new BiscuitBuilder();
        $biscuitBuilder->addCode('user({id})', ['id' => '1234']);
        $biscuit = $biscuitBuilder->build($kp->getPrivateKey());

        $authBuilder = new AuthorizerBuilder();
        $authBuilder->addCode('allow if user($u)');
        $authorizer = $authBuilder->build($biscuit);

        $rule = new Rule('u($id) <- user($id)');
        $facts = $authorizer->query($rule);

        static::assertIsArray($facts);
        static::assertCount(1, $facts);
        static::assertSame('u', $facts[0]->name());
    }

    public function testAuthorizerSnapshot(): void
    {
        $privateKeyHex = 'ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97';
        $privateKey = new PrivateKey($privateKeyHex);
        $kp = KeyPair::fromPrivateKey($privateKey);

        $biscuitBuilder = new BiscuitBuilder();
        $biscuitBuilder->addCode('user({id})', ['id' => '1234']);
        $biscuit = $biscuitBuilder->build($privateKey);

        $authBuilder = new AuthorizerBuilder();
        $authBuilder->addCode('allow if user({id})', ['id' => '1234']);
        $authorizer = $authBuilder->build($biscuit);

        $snapshot = $authorizer->base64Snapshot();
        static::assertIsString($snapshot);

        $parsed = Authorizer::fromBase64Snapshot($snapshot);
        static::assertInstanceOf(Authorizer::class, $parsed);

        $policy = $parsed->authorize();
        static::assertSame(0, $policy);

        $rawSnapshot = $authorizer->rawSnapshot();
        static::assertIsArray($rawSnapshot);

        $parsedFromRaw = Authorizer::fromRawSnapshot(pack('C*', ...$rawSnapshot));
        static::assertInstanceOf(Authorizer::class, $parsedFromRaw);

        $rawPolicy = $parsedFromRaw->authorize();
        static::assertSame(0, $rawPolicy);
    }

    public function testAuthorizerBuilderSnapshot(): void
    {
        $authBuilder = new AuthorizerBuilder();
        $authBuilder->addCode('allow if user({id})', ['id' => '1234']);

        $snapshot = $authBuilder->base64Snapshot();
        static::assertIsString($snapshot);

        $parsed = AuthorizerBuilder::fromBase64Snapshot($snapshot);
        static::assertInstanceOf(AuthorizerBuilder::class, $parsed);

        $rawSnapshot = $authBuilder->rawSnapshot();
        static::assertIsArray($rawSnapshot);

        $parsedFromRaw = AuthorizerBuilder::fromRawSnapshot(pack('C*', ...$rawSnapshot));
        static::assertInstanceOf(AuthorizerBuilder::class, $parsedFromRaw);
    }

    public function testUnverifiedBiscuit(): void
    {
        $kp = new KeyPair();
        $pubkey = new PublicKey('ed25519/acdd6d5b53bfee478bf689f8e012fe7988bf755e3d7c5152947abc149bc20189');

        $builder = new BiscuitBuilder();
        $builder->addCode('test(true)');

        $token1 = $builder->build($kp->getPrivateKey());
        $base64_1 = $token1->toBase64();

        $builder->setRootKeyId(42);
        $token2 = $builder->build($kp->getPrivateKey());
        $block = new BlockBuilder();
        $block->addCode('test(false)');
        $token2 = $token2->append($block);
        $base64_2 = $token2->toBase64();

        $utoken1 = UnverifiedBiscuit::fromBase64($base64_1);
        $utoken2 = UnverifiedBiscuit::fromBase64($base64_2);

        static::assertNull($utoken1->rootKeyId());
        static::assertSame(42, $utoken2->rootKeyId());

        static::assertSame(1, $utoken1->blockCount());
        static::assertSame(2, $utoken2->blockCount());
    }

    public function testUnverifiedBiscuitVerification(): void
    {
        $privateKeyHex = 'ed25519-private/473b5189232f3f597b5c2f3f9b0d5e28b1ee4e7cce67ec6b7fbf5984157a6b97';
        $privateKey = new PrivateKey($privateKeyHex);
        $kp = KeyPair::fromPrivateKey($privateKey);

        $builder = new BiscuitBuilder();
        $builder->addCode('user("alice")');
        $biscuit = $builder->build($privateKey);

        $base64 = $biscuit->toBase64();
        $utoken = UnverifiedBiscuit::fromBase64($base64);

        $verified = $utoken->verify($kp->getPublicKey());
        static::assertInstanceOf(Biscuit::class, $verified);
    }

    public function testUnverifiedBiscuitAppend(): void
    {
        $kp = new KeyPair();
        $builder = new BiscuitBuilder();
        $builder->addCode('user("alice")');
        $biscuit = $builder->build($kp->getPrivateKey());

        $base64 = $biscuit->toBase64();
        $utoken = UnverifiedBiscuit::fromBase64($base64);

        $block = new BlockBuilder();
        $block->addCode('check if true');
        $utoken2 = $utoken->append($block);

        static::assertSame(2, $utoken2->blockCount());
    }

    public function testRevocationIds(): void
    {
        $kp = new KeyPair();
        $builder = new BiscuitBuilder();
        $builder->addCode('user("alice")');
        $biscuit = $builder->build($kp->getPrivateKey());

        $revocationIds = $biscuit->revocationIds();
        static::assertIsArray($revocationIds);
        static::assertCount(1, $revocationIds);

        $block = new BlockBuilder();
        $block->addCode('resource("file1")');
        $biscuit2 = $biscuit->append($block);

        $revocationIds2 = $biscuit2->revocationIds();
        static::assertCount(2, $revocationIds2);
    }

    public function testThirdPartyBlocks(): void
    {
        $rootKp = new KeyPair();
        $biscuitBuilder = new BiscuitBuilder();
        $biscuitBuilder->addCode('user({id})', ['id' => '1234']);
        $biscuit = $biscuitBuilder->build($rootKp->getPrivateKey());

        $thirdPartyKp = new KeyPair();
        $newBlock = new BlockBuilder();
        $newBlock->addCode('external_fact({fact})', ['fact' => '56']);

        $thirdPartyRequest = $biscuit->thirdPartyRequest();
        static::assertInstanceOf(ThirdPartyRequest::class, $thirdPartyRequest);

        $thirdPartyBlock = $thirdPartyRequest->createBlock($thirdPartyKp->getPrivateKey(), $newBlock);
        static::assertInstanceOf(ThirdPartyBlock::class, $thirdPartyBlock);

        $biscuitWithThirdParty = $biscuit->appendThirdParty($thirdPartyKp->getPublicKey(), $thirdPartyBlock);
        static::assertInstanceOf(Biscuit::class, $biscuitWithThirdParty);

        static::assertSame(2, $biscuitWithThirdParty->blockCount());

        $externalKey = $biscuitWithThirdParty->blockExternalKey(1);
        static::assertInstanceOf(PublicKey::class, $externalKey);
        static::assertSame($thirdPartyKp->getPublicKey()->toHex(), $externalKey->toHex());
    }

    public function testPEMKeyImport(): void
    {
        $privatePem = "-----BEGIN PRIVATE KEY-----\nMC4CAQAwBQYDK2VwBCIEIASZaU0NoF3KxABSZj5x1QwVOUZfiSbf6SAzz3qq1T1l\n-----END PRIVATE KEY-----";
        $privateKeyHex = 'ed25519-private/0499694d0da05dcac40052663e71d50c1539465f8926dfe92033cf7aaad53d65';

        $privateKey = PrivateKey::fromPem($privatePem);
        static::assertSame($privateKeyHex, $privateKey->toHex());

        $kp = KeyPair::fromPrivateKey($privateKey);
        static::assertInstanceOf(KeyPair::class, $kp);
    }

    public function testDERKeyImport(): void
    {
        $privateDer = hex2bin(
            '302e020100300506032b6570042204200499694d0da05dcac40052663e71d50c1539465f8926dfe92033cf7aaad53d65',
        );
        $privateKeyHex = 'ed25519-private/0499694d0da05dcac40052663e71d50c1539465f8926dfe92033cf7aaad53d65';

        $privateKey = PrivateKey::fromDer($privateDer);
        static::assertSame($privateKeyHex, $privateKey->toHex());

        $kp = KeyPair::fromPrivateKey($privateKey);
        static::assertInstanceOf(KeyPair::class, $kp);
    }

    public function testSetRootKeyId(): void
    {
        $kp = new KeyPair();
        $builder = new BiscuitBuilder();
        $builder->addCode('user("alice")');
        $builder->setRootKeyId(42);

        $biscuit = $builder->build($kp->getPrivateKey());
        $base64 = $biscuit->toBase64();

        $utoken = UnverifiedBiscuit::fromBase64($base64);
        static::assertSame(42, $utoken->rootKeyId());
    }

    public function testFactWithSet(): void
    {
        $fact = new Fact('user({name})');
        $fact->set('name', 'alice');

        static::assertInstanceOf(Fact::class, $fact);
    }

    public function testRuleWithSet(): void
    {
        $rule = new Rule('can_read($u, {res}) <- user($u), resource({res})');
        $rule->set('res', 'file1');

        static::assertInstanceOf(Rule::class, $rule);
    }

    public function testCheckWithSet(): void
    {
        $check = new Check('check if user({username})');
        $check->set('username', 'alice');

        static::assertInstanceOf(Check::class, $check);
    }

    public function testPolicyWithSet(): void
    {
        $policy = new Policy('allow if user({username})');
        $policy->set('username', 'alice');

        static::assertInstanceOf(Policy::class, $policy);
    }

    public function testRuleConstructorWithParams(): void
    {
        $rule = new Rule('can_read($u, {res}) <- user($u)', ['res' => 'file1']);
        static::assertStringContainsString('can_read($u, "file1")', (string) $rule);
    }

    public function testCheckConstructorWithParams(): void
    {
        $check = new Check('check if user({username})', ['username' => 'alice']);
        static::assertStringContainsString('user("alice")', (string) $check);
    }

    public function testPolicyConstructorWithParams(): void
    {
        $policy = new Policy('allow if user({username})', ['username' => 'alice']);
        static::assertStringContainsString('user("alice")', (string) $policy);
    }

    public function testBlockMerge(): void
    {
        $builder1 = new BlockBuilder();
        $builder1->addCode('user("alice")');

        $builder2 = new BlockBuilder();
        $builder2->addCode('resource("file1")');

        $builder1->merge($builder2);

        static::assertInstanceOf(BlockBuilder::class, $builder1);
    }

    public function testAuthorizerBuilderMerge(): void
    {
        $builder1 = new AuthorizerBuilder();
        $builder1->addCode('user("alice")');

        $builder2 = new AuthorizerBuilder();
        $builder2->addCode('resource("file1")');

        $builder1->merge($builder2);

        static::assertInstanceOf(AuthorizerBuilder::class, $builder1);
    }

    public function testAuthorizerBuilderMergeBlock(): void
    {
        $authBuilder = new AuthorizerBuilder();
        $authBuilder->addCode('user("alice")');

        $blockBuilder = new BlockBuilder();
        $blockBuilder->addCode('resource("file1")');

        $authBuilder->mergeBlock($blockBuilder);

        static::assertInstanceOf(AuthorizerBuilder::class, $authBuilder);
    }
}
