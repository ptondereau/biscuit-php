<?php

// Stubs for biscuit_php

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
         * @return \Biscuit\Auth\MatchedPolicy
         */
        public function authorize(): \Biscuit\Auth\MatchedPolicy {}

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

    class FailedCheck {
        public function __construct() {}

        /**
         * @return int|null
         */
        public function getBlockId(): ?int {}

        /**
         * @return int
         */
        public function getCheckId(): int {}

        /**
         * @return string
         */
        public function getOrigin(): string {}

        /**
         * @return string
         */
        public function getRule(): string {}
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

    class MatchedPolicy {
        public function __construct() {}

        /**
         * @return string|null
         */
        public function getCode(): ?string {}

        /**
         * @return string
         */
        public function getKind(): string {}

        /**
         * @return int
         */
        public function getPolicyId(): int {}
    }

    class ParseError {
        public function __construct() {}

        /**
         * @return string
         */
        public function getInput(): string {}

        /**
         * @return string|null
         */
        public function getMessage(): ?string {}
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
    class AuthorizationException extends Biscuit\Exception\BiscuitException {
        protected string $message;

        public function __construct() {}

        /**
         * @return array
         */
        public function getFailedChecks(): array {}

        /**
         * @return \Biscuit\Auth\MatchedPolicy|null
         */
        public function getMatchedPolicy(): ?\Biscuit\Auth\MatchedPolicy {}
    }

    class AuthorizerBuildException extends Biscuit\Exception\BuildException {
        public function __construct() {}
    }

    class Base64Exception extends Biscuit\Exception\FormatException {
        public function __construct() {}
    }

    class BiscuitBuildException extends Biscuit\Exception\BuildException {
        public function __construct() {}
    }

    class BiscuitException extends \Exception {
        public function __construct() {}
    }

    class BlockAppendException extends Biscuit\Exception\BuildException {
        public function __construct() {}
    }

    class BuildException extends Biscuit\Exception\BiscuitException {
        public function __construct() {}
    }

    class BuilderStateException extends Biscuit\Exception\BiscuitException {
        public function __construct() {}
    }

    class BytesException extends Biscuit\Exception\FormatException {
        public function __construct() {}
    }

    class CheckException extends Biscuit\Exception\DatalogException {
        protected string $message;

        public function __construct() {}

        /**
         * @return array|null
         */
        public function getMissingParameters(): ?array {}

        /**
         * @return array|null
         */
        public function getParseErrors(): ?array {}

        /**
         * @return array|null
         */
        public function getUnusedParameters(): ?array {}
    }

    class DatalogException extends Biscuit\Exception\BiscuitException {
        public function __construct() {}

        /**
         * @return array|null
         */
        public function getMissingParameters(): ?array {}

        /**
         * @return array|null
         */
        public function getParseErrors(): ?array {}

        /**
         * @return array|null
         */
        public function getUnusedParameters(): ?array {}
    }

    class FactException extends Biscuit\Exception\DatalogException {
        protected string $message;

        public function __construct() {}

        /**
         * @return array|null
         */
        public function getMissingParameters(): ?array {}

        /**
         * @return array|null
         */
        public function getParseErrors(): ?array {}

        /**
         * @return array|null
         */
        public function getUnusedParameters(): ?array {}
    }

    class FormatException extends Biscuit\Exception\BiscuitException {
        public function __construct() {}
    }

    class KeyException extends Biscuit\Exception\BiscuitException {
        public function __construct() {}
    }

    class PolicyException extends Biscuit\Exception\DatalogException {
        protected string $message;

        public function __construct() {}

        /**
         * @return array|null
         */
        public function getMissingParameters(): ?array {}

        /**
         * @return array|null
         */
        public function getParseErrors(): ?array {}

        /**
         * @return array|null
         */
        public function getUnusedParameters(): ?array {}
    }

    class PrivateKeyException extends Biscuit\Exception\KeyException {
        public function __construct() {}
    }

    class PublicKeyException extends Biscuit\Exception\KeyException {
        public function __construct() {}
    }

    class RuleException extends Biscuit\Exception\DatalogException {
        protected string $message;

        public function __construct() {}

        /**
         * @return array|null
         */
        public function getMissingParameters(): ?array {}

        /**
         * @return array|null
         */
        public function getParseErrors(): ?array {}

        /**
         * @return array|null
         */
        public function getUnusedParameters(): ?array {}
    }

    class ScopeException extends Biscuit\Exception\DatalogException {
        protected string $message;

        public function __construct() {}

        /**
         * @return array|null
         */
        public function getMissingParameters(): ?array {}

        /**
         * @return array|null
         */
        public function getParseErrors(): ?array {}

        /**
         * @return array|null
         */
        public function getUnusedParameters(): ?array {}
    }

    class SignatureException extends Biscuit\Exception\FormatException {
        public function __construct() {}
    }

    class SnapshotException extends Biscuit\Exception\FormatException {
        public function __construct() {}
    }

    class TermException extends Biscuit\Exception\DatalogException {
        protected string $message;

        public function __construct() {}

        /**
         * @return array|null
         */
        public function getMissingParameters(): ?array {}

        /**
         * @return array|null
         */
        public function getParseErrors(): ?array {}

        /**
         * @return array|null
         */
        public function getUnusedParameters(): ?array {}
    }

    class ThirdPartyBlockAppendException extends Biscuit\Exception\BuildException {
        public function __construct() {}
    }

    class ThirdPartyException extends Biscuit\Exception\BiscuitException {
        public function __construct() {}
    }
}
