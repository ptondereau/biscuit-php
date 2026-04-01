<?php

// Stubs for biscuit-php

namespace Biscuit\Auth {
    enum Algorithm: int {
      case Ed25519 = 0;
      case Secp256r1 = 1;
    }

    class Authorizer {
        public function __construct() {}

        /**
         * @return string
         */
        public function __toString(): string {}

        /**
         * @return int
         */
        public function authorize(): int {}

        /**
         * @return string
         */
        public function base64Snapshot(): string {}

        /**
         * @param string $input
         * @return \Biscuit\Auth\Authorizer
         */
        public static function fromBase64Snapshot(string $input): \Biscuit\Auth\Authorizer {}

        /**
         * @param string $input
         * @return \Biscuit\Auth\Authorizer
         */
        public static function fromRawSnapshot(string $input): \Biscuit\Auth\Authorizer {}

        /**
         * @param \Biscuit\Auth\Rule $rule
         * @return array
         */
        public function query(\Biscuit\Auth\Rule $rule): array {}

        /**
         * @return array
         */
        public function rawSnapshot(): array {}
    }

    class AuthorizerBuilder {
        /**
         * @param string|null $source
         * @param array|null $params
         * @param array|null $scope_params
         */
        public function __construct(?string $source = null, ?array $params = null, ?array $scope_params = null) {}

        /**
         * @return string
         */
        public function __toString(): string {}

        /**
         * @param \Biscuit\Auth\Check $check
         * @return void
         */
        public function addCheck(\Biscuit\Auth\Check $check): void {}

        /**
         * @param string $source
         * @param array|null $params
         * @param array|null $scope_params
         * @return void
         */
        public function addCode(string $source, ?array $params = null, ?array $scope_params = null): void {}

        /**
         * @param \Biscuit\Auth\Fact $fact
         * @return void
         */
        public function addFact(\Biscuit\Auth\Fact $fact): void {}

        /**
         * @param \Biscuit\Auth\Policy $policy
         * @return void
         */
        public function addPolicy(\Biscuit\Auth\Policy $policy): void {}

        /**
         * @param \Biscuit\Auth\Rule $rule
         * @return void
         */
        public function addRule(\Biscuit\Auth\Rule $rule): void {}

        /**
         * @return string
         */
        public function base64Snapshot(): string {}

        /**
         * @param \Biscuit\Auth\Biscuit $token
         * @return \Biscuit\Auth\Authorizer
         */
        public function build(\Biscuit\Auth\Biscuit $token): \Biscuit\Auth\Authorizer {}

        /**
         * @return \Biscuit\Auth\Authorizer
         */
        public function buildUnauthenticated(): \Biscuit\Auth\Authorizer {}

        /**
         * @param string $input
         * @return \Biscuit\Auth\AuthorizerBuilder
         */
        public static function fromBase64Snapshot(string $input): \Biscuit\Auth\AuthorizerBuilder {}

        /**
         * @param string $input
         * @return \Biscuit\Auth\AuthorizerBuilder
         */
        public static function fromRawSnapshot(string $input): \Biscuit\Auth\AuthorizerBuilder {}

        /**
         * @param \Biscuit\Auth\AuthorizerBuilder $other
         * @return void
         */
        public function merge(\Biscuit\Auth\AuthorizerBuilder $other): void {}

        /**
         * @param \Biscuit\Auth\BlockBuilder $block
         * @return void
         */
        public function mergeBlock(\Biscuit\Auth\BlockBuilder $block): void {}

        /**
         * @return array
         */
        public function rawSnapshot(): array {}

        /**
         * @return void
         */
        public function setTime(): void {}
    }

    class Biscuit {
        public function __construct() {}

        /**
         * @return string
         */
        public function __toString(): string {}

        /**
         * @param \Biscuit\Auth\BlockBuilder $block
         * @return \Biscuit\Auth\Biscuit
         */
        public function append(\Biscuit\Auth\BlockBuilder $block): \Biscuit\Auth\Biscuit {}

        /**
         * @param \Biscuit\Auth\PublicKey $external_key
         * @param \Biscuit\Auth\ThirdPartyBlock $block
         * @return \Biscuit\Auth\Biscuit
         */
        public function appendThirdParty(\Biscuit\Auth\PublicKey $external_key, \Biscuit\Auth\ThirdPartyBlock $block): \Biscuit\Auth\Biscuit {}

        /**
         * @return int
         */
        public function blockCount(): int {}

        /**
         * @param int $index
         * @return \Biscuit\Auth\PublicKey|null
         */
        public function blockExternalKey(int $index): ?\Biscuit\Auth\PublicKey {}

        /**
         * @param int $index
         * @return string
         */
        public function blockSource(int $index): string {}

        /**
         * @return \Biscuit\Auth\BiscuitBuilder
         */
        public static function builder(): \Biscuit\Auth\BiscuitBuilder {}

        /**
         * @param string $data
         * @param \Biscuit\Auth\PublicKey $root
         * @return \Biscuit\Auth\Biscuit
         */
        public static function fromBase64(string $data, \Biscuit\Auth\PublicKey $root): \Biscuit\Auth\Biscuit {}

        /**
         * @param string $data
         * @param \Biscuit\Auth\PublicKey $root
         * @return \Biscuit\Auth\Biscuit
         */
        public static function fromBytes(string $data, \Biscuit\Auth\PublicKey $root): \Biscuit\Auth\Biscuit {}

        /**
         * @return array
         */
        public function revocationIds(): array {}

        /**
         * @return \Biscuit\Auth\ThirdPartyRequest
         */
        public function thirdPartyRequest(): \Biscuit\Auth\ThirdPartyRequest {}

        /**
         * @return string
         */
        public function toBase64(): string {}

        /**
         * @return array
         */
        public function toBytes(): array {}
    }

    class BiscuitBuilder {
        /**
         * @param string|null $source
         * @param array|null $params
         * @param array|null $scope_params
         */
        public function __construct(?string $source = null, ?array $params = null, ?array $scope_params = null) {}

        /**
         * @return string
         */
        public function __toString(): string {}

        /**
         * @param \Biscuit\Auth\Check $check
         * @return void
         */
        public function addCheck(\Biscuit\Auth\Check $check): void {}

        /**
         * @param string $source
         * @param array|null $params
         * @param array|null $scope_params
         * @return void
         */
        public function addCode(string $source, ?array $params = null, ?array $scope_params = null): void {}

        /**
         * @param \Biscuit\Auth\Fact $fact
         * @return void
         */
        public function addFact(\Biscuit\Auth\Fact $fact): void {}

        /**
         * @param \Biscuit\Auth\Rule $rule
         * @return void
         */
        public function addRule(\Biscuit\Auth\Rule $rule): void {}

        /**
         * @param \Biscuit\Auth\PrivateKey $root
         * @return \Biscuit\Auth\Biscuit
         */
        public function build(\Biscuit\Auth\PrivateKey $root): \Biscuit\Auth\Biscuit {}

        /**
         * @param \Biscuit\Auth\BlockBuilder $other
         * @return void
         */
        public function merge(\Biscuit\Auth\BlockBuilder $other): void {}

        /**
         * @param int $root_key_id
         * @return void
         */
        public function setRootKeyId(int $root_key_id): void {}
    }

    class BlockBuilder {
        /**
         * @param string|null $source
         * @param array|null $params
         * @param array|null $scope_params
         */
        public function __construct(?string $source = null, ?array $params = null, ?array $scope_params = null) {}

        /**
         * @return string
         */
        public function __toString(): string {}

        /**
         * @param \Biscuit\Auth\Check $check
         * @return void
         */
        public function addCheck(\Biscuit\Auth\Check $check): void {}

        /**
         * @param string $source
         * @param array|null $params
         * @param array|null $scope_params
         * @return void
         */
        public function addCode(string $source, ?array $params = null, ?array $scope_params = null): void {}

        /**
         * @param \Biscuit\Auth\Fact $fact
         * @return void
         */
        public function addFact(\Biscuit\Auth\Fact $fact): void {}

        /**
         * @param \Biscuit\Auth\Rule $rule
         * @return void
         */
        public function addRule(\Biscuit\Auth\Rule $rule): void {}

        /**
         * @param \Biscuit\Auth\BlockBuilder $other
         * @return void
         */
        public function merge(\Biscuit\Auth\BlockBuilder $other): void {}
    }

    class Check {
        /**
         * @param string $source
         * @param array|null $params
         * @param array|null $scope_params
         */
        public function __construct(string $source, ?array $params = null, ?array $scope_params = null) {}

        /**
         * @return string
         */
        public function __toString(): string {}

        /**
         * @param int|string|bool|null $value
         *
         * @param string $name
         * @param mixed $value
         * @return void
         */
        public function set(string $name, mixed $value): void {}

        /**
         * @param string $name
         * @param \Biscuit\Auth\PublicKey $key
         * @return void
         */
        public function setScope(string $name, \Biscuit\Auth\PublicKey $key): void {}
    }

    class Fact {
        /**
         * @param string $source
         * @param array|null $params
         */
        public function __construct(string $source, ?array $params = null) {}

        /**
         * @return string
         */
        public function __toString(): string {}

        /**
         * @return string
         */
        public function name(): string {}

        /**
         * @param int|string|bool|null $value
         *
         * @param string $name
         * @param mixed $value
         * @return void
         */
        public function set(string $name, mixed $value): void {}
    }

    class KeyPair {
        /**
         * @param \Biscuit\Auth\Algorithm|null $alg
         */
        public function __construct(?\Biscuit\Auth\Algorithm $alg = null) {}

        /**
         * @param \Biscuit\Auth\PrivateKey $private_key
         * @return \Biscuit\Auth\KeyPair
         */
        public static function fromPrivateKey(\Biscuit\Auth\PrivateKey $private_key): \Biscuit\Auth\KeyPair {}

        /**
         * @return \Biscuit\Auth\PrivateKey
         */
        public function getPrivateKey(): \Biscuit\Auth\PrivateKey {}

        /**
         * @return \Biscuit\Auth\PublicKey
         */
        public function getPublicKey(): \Biscuit\Auth\PublicKey {}
    }

    class Policy {
        /**
         * @param string $source
         * @param array|null $params
         * @param array|null $scope_params
         */
        public function __construct(string $source, ?array $params = null, ?array $scope_params = null) {}

        /**
         * @return string
         */
        public function __toString(): string {}

        /**
         * @param int|string|bool|null $value
         *
         * @param string $name
         * @param mixed $value
         * @return void
         */
        public function set(string $name, mixed $value): void {}

        /**
         * @param string $name
         * @param \Biscuit\Auth\PublicKey $key
         * @return void
         */
        public function setScope(string $name, \Biscuit\Auth\PublicKey $key): void {}
    }

    class PrivateKey {
        /**
         * @param string $data
         */
        public function __construct(string $data) {}

        /**
         * @return string
         */
        public function __toString(): string {}

        /**
         * @param string $data
         * @param \Biscuit\Auth\Algorithm|null $alg
         * @return \Biscuit\Auth\PrivateKey
         */
        public static function fromBytes(string $data, ?\Biscuit\Auth\Algorithm $alg = null): \Biscuit\Auth\PrivateKey {}

        /**
         * @param string $der
         * @return \Biscuit\Auth\PrivateKey
         */
        public static function fromDer(string $der): \Biscuit\Auth\PrivateKey {}

        /**
         * @param string $pem
         * @return \Biscuit\Auth\PrivateKey
         */
        public static function fromPem(string $pem): \Biscuit\Auth\PrivateKey {}

        /**
         * @param \Biscuit\Auth\Algorithm|null $alg
         * @return \Biscuit\Auth\PrivateKey
         */
        public static function generate(?\Biscuit\Auth\Algorithm $alg = null): \Biscuit\Auth\PrivateKey {}

        /**
         * @return \Biscuit\Auth\PublicKey
         */
        public function getPublicKey(): \Biscuit\Auth\PublicKey {}

        /**
         * @return array
         */
        public function toBytes(): array {}

        /**
         * @return string
         */
        public function toHex(): string {}
    }

    class PublicKey {
        /**
         * @param string $data
         */
        public function __construct(string $data) {}

        /**
         * @return string
         */
        public function __toString(): string {}

        /**
         * @param string $data
         * @param \Biscuit\Auth\Algorithm|null $alg
         * @return \Biscuit\Auth\PublicKey
         */
        public static function fromBytes(string $data, ?\Biscuit\Auth\Algorithm $alg = null): \Biscuit\Auth\PublicKey {}

        /**
         * @param string $der
         * @return \Biscuit\Auth\PublicKey
         */
        public static function fromDer(string $der): \Biscuit\Auth\PublicKey {}

        /**
         * @param string $pem
         * @return \Biscuit\Auth\PublicKey
         */
        public static function fromPem(string $pem): \Biscuit\Auth\PublicKey {}

        /**
         * @return array
         */
        public function toBytes(): array {}

        /**
         * @return string
         */
        public function toHex(): string {}
    }

    class Rule {
        /**
         * @param string $source
         * @param array|null $params
         * @param array|null $scope_params
         */
        public function __construct(string $source, ?array $params = null, ?array $scope_params = null) {}

        /**
         * @return string
         */
        public function __toString(): string {}

        /**
         * @param int|string|bool|null $value
         *
         * @param string $name
         * @param mixed $value
         * @return void
         */
        public function set(string $name, mixed $value): void {}

        /**
         * @param string $name
         * @param \Biscuit\Auth\PublicKey $key
         * @return void
         */
        public function setScope(string $name, \Biscuit\Auth\PublicKey $key): void {}
    }

    class ThirdPartyBlock {
        public function __construct() {}
    }

    class ThirdPartyRequest {
        public function __construct() {}

        /**
         * @param \Biscuit\Auth\PrivateKey $private_key
         * @param \Biscuit\Auth\BlockBuilder $block
         * @return \Biscuit\Auth\ThirdPartyBlock
         */
        public function createBlock(\Biscuit\Auth\PrivateKey $private_key, \Biscuit\Auth\BlockBuilder $block): \Biscuit\Auth\ThirdPartyBlock {}
    }

    class UnverifiedBiscuit {
        public function __construct() {}

        /**
         * @param \Biscuit\Auth\BlockBuilder $block
         * @return \Biscuit\Auth\UnverifiedBiscuit
         */
        public function append(\Biscuit\Auth\BlockBuilder $block): \Biscuit\Auth\UnverifiedBiscuit {}

        /**
         * @return int
         */
        public function blockCount(): int {}

        /**
         * @param int $index
         * @return string
         */
        public function blockSource(int $index): string {}

        /**
         * @param string $data
         * @return \Biscuit\Auth\UnverifiedBiscuit
         */
        public static function fromBase64(string $data): \Biscuit\Auth\UnverifiedBiscuit {}

        /**
         * @return array
         */
        public function revocationIds(): array {}

        /**
         * @return int|null
         */
        public function rootKeyId(): ?int {}

        /**
         * @param \Biscuit\Auth\PublicKey $root
         * @return \Biscuit\Auth\Biscuit
         */
        public function verify(\Biscuit\Auth\PublicKey $root): \Biscuit\Auth\Biscuit {}
    }
}

namespace Biscuit\Exception {
    class AuthorizerError extends \Exception {
        public function __construct() {}
    }

    class BuilderConsumed extends \Exception {
        public function __construct() {}
    }

    class InvalidCheck extends \Exception {
        public function __construct() {}
    }

    class InvalidFact extends \Exception {
        public function __construct() {}
    }

    class InvalidPolicy extends \Exception {
        public function __construct() {}
    }

    class InvalidPrivateKey extends \Exception {
        public function __construct() {}
    }

    class InvalidPublicKey extends \Exception {
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
}
