<?php

// Stubs for biscuit-php

namespace Biscuit\Auth {
    class Biscuit {
        public static function builder(): \Biscuit\Auth\BiscuitBuilder {}

        public static function fromBytes(string $data, \Biscuit\Auth\PublicKey $root): \Biscuit\Auth\Biscuit {}

        public static function fromBase64(string $data, \Biscuit\Auth\PublicKey $root): \Biscuit\Auth\Biscuit {}

        public function toBytes(): array {}

        public function toBase64(): string {}

        public function blockCount(): int {}

        public function blockSource(int $index): string {}

        public function append(\Biscuit\Auth\BlockBuilder $block): \Biscuit\Auth\Biscuit {}

        public function appendThirdParty(\Biscuit\Auth\PublicKey $external_key, \Biscuit\Auth\ThirdPartyBlock $block): \Biscuit\Auth\Biscuit {}

        public function thirdPartyRequest(): \Biscuit\Auth\ThirdPartyRequest {}

        public function revocationIds(): array {}

        public function blockExternalKey(int $index): ?\Biscuit\Auth\PublicKey {}

        public function __toString(): string {}

        public function __construct() {}
    }

    class UnverifiedBiscuit {
        public static function fromBase64(string $data): \Biscuit\Auth\UnverifiedBiscuit {}

        public function rootKeyId(): ?int {}

        public function blockCount(): int {}

        public function blockSource(int $index): string {}

        public function append(\Biscuit\Auth\BlockBuilder $block): \Biscuit\Auth\UnverifiedBiscuit {}

        public function revocationIds(): array {}

        public function verify(\Biscuit\Auth\PublicKey $root): \Biscuit\Auth\Biscuit {}

        public function __construct() {}
    }

    class Authorizer {
        public function authorize(): int {}

        public function query(\Biscuit\Auth\Rule $rule): array {}

        public function base64Snapshot(): string {}

        public function rawSnapshot(): array {}

        public static function fromBase64Snapshot(string $input): \Biscuit\Auth\Authorizer {}

        public static function fromRawSnapshot(string $input): \Biscuit\Auth\Authorizer {}

        public function __toString(): string {}

        public function __construct() {}
    }

    class AuthorizerBuilder {
        public function addCode(string $source, ?array $params = null, ?array $scope_params = null): void {}

        public function addFact(\Biscuit\Auth\Fact $fact): void {}

        public function addRule(\Biscuit\Auth\Rule $rule): mixed {}

        public function addCheck(\Biscuit\Auth\Check $check): mixed {}

        public function addPolicy(\Biscuit\Auth\Policy $policy): mixed {}

        public function setTime() {}

        public function merge(\Biscuit\Auth\AuthorizerBuilder $other) {}

        public function mergeBlock(\Biscuit\Auth\BlockBuilder $block) {}

        public function base64Snapshot(): string {}

        public function rawSnapshot(): array {}

        public static function fromBase64Snapshot(string $input): \Biscuit\Auth\AuthorizerBuilder {}

        public static function fromRawSnapshot(string $input): \Biscuit\Auth\AuthorizerBuilder {}

        public function build(\Biscuit\Auth\Biscuit $token): \Biscuit\Auth\Authorizer {}

        public function buildUnauthenticated(): \Biscuit\Auth\Authorizer {}

        public function __toString(): string {}

        public function __construct(?string $source = null, ?array $params = null, ?array $scope_params = null) {}
    }

    class BiscuitBuilder {
        public function build(\Biscuit\Auth\PrivateKey $root): \Biscuit\Auth\Biscuit {}

        public function addCode(string $source, ?array $params = null, ?array $scope_params = null): void {}

        public function merge(\Biscuit\Auth\BlockBuilder $other): void {}

        public function addFact(\Biscuit\Auth\Fact $fact): mixed {}

        public function addRule(\Biscuit\Auth\Rule $rule): mixed {}

        public function addCheck(\Biscuit\Auth\Check $check): mixed {}

        public function setRootKeyId(int $root_key_id) {}

        public function __toString(): string {}

        public function __construct(?string $source = null, ?array $params = null, ?array $scope_params = null) {}
    }

    class BlockBuilder {
        public function addFact(\Biscuit\Auth\Fact $fact): void {}

        public function addRule(\Biscuit\Auth\Rule $rule): void {}

        public function addCheck(\Biscuit\Auth\Check $check): void {}

        public function addCode(string $source, ?array $params = null, ?array $scope_params = null): void {}

        public function merge(\Biscuit\Auth\BlockBuilder $other): void {}

        public function __toString(): string {}

        public function __construct(?string $source = null, ?array $params = null, ?array $scope_params = null) {}
    }

    class ThirdPartyRequest {
        public function createBlock(\Biscuit\Auth\PrivateKey $private_key, \Biscuit\Auth\BlockBuilder $block): \Biscuit\Auth\ThirdPartyBlock {}

        public function __construct() {}
    }

    class ThirdPartyBlock {
        public function __construct() {}
    }

    class Rule {
        /**
         * @param int|string|bool|null $value
         */
        public function set(string $name, mixed $value): mixed {}

        public function setScope(string $name, \Biscuit\Auth\PublicKey $key): mixed {}

        public function __toString(): string {}

        public function __construct(string $source, ?array $params = null, ?array $scope_params = null) {}
    }

    class Fact {
        /**
         * @param int|string|bool|null $value
         */
        public function set(string $name, mixed $value): mixed {}

        public function name(): string {}

        public function __toString(): string {}

        public function __construct(string $source, ?array $params = null) {}
    }

    class Check {
        /**
         * @param int|string|bool|null $value
         */
        public function set(string $name, mixed $value): mixed {}

        public function setScope(string $name, \Biscuit\Auth\PublicKey $key): mixed {}

        public function __toString(): string {}

        public function __construct(string $source, ?array $params = null, ?array $scope_params = null) {}
    }

    class Policy {
        /**
         * @param int|string|bool|null $value
         */
        public function set(string $name, mixed $value): mixed {}

        public function setScope(string $name, \Biscuit\Auth\PublicKey $key): mixed {}

        public function __toString(): string {}

        public function __construct(string $source, ?array $params = null, ?array $scope_params = null) {}
    }

    class KeyPair {
        public static function fromPrivateKey(\Biscuit\Auth\PrivateKey $private_key): \Biscuit\Auth\KeyPair {}

        public function getPublicKey(): \Biscuit\Auth\PublicKey {}

        public function getPrivateKey(): \Biscuit\Auth\PrivateKey {}

        public function __construct(?\Biscuit\Auth\Algorithm $alg = null) {}
    }

    class PublicKey {
        public static function fromBytes(string $data, ?\Biscuit\Auth\Algorithm $alg = null): \Biscuit\Auth\PublicKey {}

        public static function fromPem(string $pem): \Biscuit\Auth\PublicKey {}

        public static function fromDer(string $der): \Biscuit\Auth\PublicKey {}

        public function toBytes(): array {}

        public function toHex(): string {}

        public function __toString(): string {}

        public function __construct(string $data) {}
    }

    class PrivateKey {
        public static function fromBytes(string $data, ?\Biscuit\Auth\Algorithm $alg = null): \Biscuit\Auth\PrivateKey {}

        public static function fromPem(string $pem): \Biscuit\Auth\PrivateKey {}

        public static function fromDer(string $der): \Biscuit\Auth\PrivateKey {}

        public function toBytes(): array {}

        public function toHex(): string {}

        public function __toString(): string {}

        public function __construct(string $data) {}
    }

    /**
     * Algorithm enum for cryptographic key operations
     */
    enum Algorithm: int {
      case Ed25519 = 0;
      case Secp256r1 = 1;
    }
}

namespace Biscuit\Exception {
    class InvalidPrivateKey extends \Exception {
        public function __construct() {}
    }

    class InvalidPublicKey extends \Exception {
        public function __construct() {}
    }

    class InvalidCheck extends \Exception {
        public function __construct() {}
    }

    class InvalidPolicy extends \Exception {
        public function __construct() {}
    }

    class InvalidFact extends \Exception {
        public function __construct() {}
    }

    class InvalidRule extends \Exception {
        public function __construct() {}
    }

    class InvalidTerm extends \Exception {
        public function __construct() {}
    }

    class ThirdPartyRequestError extends \Exception {
        public function __construct() {}
    }

    class AuthorizerError extends \Exception {
        public function __construct() {}
    }
}
