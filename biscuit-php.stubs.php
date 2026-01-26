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

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the block builder has already been consumed
         */
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

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the block builder has already been consumed
         */
        public function append(\Biscuit\Auth\BlockBuilder $block): \Biscuit\Auth\UnverifiedBiscuit {}

        public function revocationIds(): array {}

        public function verify(\Biscuit\Auth\PublicKey $root): \Biscuit\Auth\Biscuit {}

        public function __construct() {}
    }

    class Authorizer {
        /**
         * @throws \Biscuit\Exception\AuthorizerError If authorization fails
         */
        public function authorize(): int {}

        /**
         * @throws \Biscuit\Exception\AuthorizerError If the query fails
         */
        public function query(\Biscuit\Auth\Rule $rule): array {}

        public function base64Snapshot(): string {}

        public function rawSnapshot(): array {}

        public static function fromBase64Snapshot(string $input): \Biscuit\Auth\Authorizer {}

        public static function fromRawSnapshot(string $input): \Biscuit\Auth\Authorizer {}

        public function __toString(): string {}

        public function __construct() {}
    }

    class AuthorizerBuilder {
        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         * @throws \Biscuit\Exception\AuthorizerError If the code is invalid
         */
        public function addCode(string $source, ?array $params = null, ?array $scope_params = null): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         * @throws \Biscuit\Exception\AuthorizerError If the fact is invalid
         */
        public function addFact(\Biscuit\Auth\Fact $fact): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         * @throws \Biscuit\Exception\AuthorizerError If the rule is invalid
         */
        public function addRule(\Biscuit\Auth\Rule $rule): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         * @throws \Biscuit\Exception\AuthorizerError If the check is invalid
         */
        public function addCheck(\Biscuit\Auth\Check $check): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         * @throws \Biscuit\Exception\AuthorizerError If the policy is invalid
         */
        public function addPolicy(\Biscuit\Auth\Policy $policy): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         */
        public function setTime(): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If either builder has already been consumed
         */
        public function merge(\Biscuit\Auth\AuthorizerBuilder $other): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If either builder has already been consumed
         */
        public function mergeBlock(\Biscuit\Auth\BlockBuilder $block): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         */
        public function base64Snapshot(): string {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         */
        public function rawSnapshot(): array {}

        public static function fromBase64Snapshot(string $input): \Biscuit\Auth\AuthorizerBuilder {}

        public static function fromRawSnapshot(string $input): \Biscuit\Auth\AuthorizerBuilder {}

        /**
         * Creates an Authorizer from the builder. The builder can be reused after this call.
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed by a merge operation
         */
        public function build(\Biscuit\Auth\Biscuit $token): \Biscuit\Auth\Authorizer {}

        /**
         * Creates an Authorizer without a token. The builder can be reused after this call.
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed by a merge operation
         */
        public function buildUnauthenticated(): \Biscuit\Auth\Authorizer {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         */
        public function __toString(): string {}

        /**
         * @throws \Biscuit\Exception\AuthorizerError If the source code is invalid
         */
        public function __construct(?string $source = null, ?array $params = null, ?array $scope_params = null) {}
    }

    class BiscuitBuilder {
        /**
         * Creates a Biscuit token from the builder. The builder can be reused after this call.
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed by a merge operation
         */
        public function build(\Biscuit\Auth\PrivateKey $root): \Biscuit\Auth\Biscuit {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         * @throws \Biscuit\Exception\InvalidTerm If a parameter value is invalid
         */
        public function addCode(string $source, ?array $params = null, ?array $scope_params = null): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If either builder has already been consumed
         */
        public function merge(\Biscuit\Auth\BlockBuilder $other): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         * @throws \Biscuit\Exception\InvalidFact If the fact is invalid
         */
        public function addFact(\Biscuit\Auth\Fact $fact): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         * @throws \Biscuit\Exception\InvalidRule If the rule is invalid
         */
        public function addRule(\Biscuit\Auth\Rule $rule): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         * @throws \Biscuit\Exception\InvalidCheck If the check is invalid
         */
        public function addCheck(\Biscuit\Auth\Check $check): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         */
        public function setRootKeyId(int $root_key_id): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         */
        public function __toString(): string {}

        /**
         * @throws \Biscuit\Exception\InvalidTerm If the source code contains invalid terms
         */
        public function __construct(?string $source = null, ?array $params = null, ?array $scope_params = null) {}
    }

    class BlockBuilder {
        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         * @throws \Biscuit\Exception\InvalidFact If the fact is invalid
         */
        public function addFact(\Biscuit\Auth\Fact $fact): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         * @throws \Biscuit\Exception\InvalidRule If the rule is invalid
         */
        public function addRule(\Biscuit\Auth\Rule $rule): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         * @throws \Biscuit\Exception\InvalidCheck If the check is invalid
         */
        public function addCheck(\Biscuit\Auth\Check $check): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         * @throws \Biscuit\Exception\InvalidTerm If a parameter value is invalid
         */
        public function addCode(string $source, ?array $params = null, ?array $scope_params = null): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If either builder has already been consumed
         */
        public function merge(\Biscuit\Auth\BlockBuilder $other): void {}

        /**
         * @throws \Biscuit\Exception\BuilderConsumed If the builder has already been consumed
         */
        public function __toString(): string {}

        /**
         * @throws \Biscuit\Exception\InvalidTerm If the source code contains invalid terms
         */
        public function __construct(?string $source = null, ?array $params = null, ?array $scope_params = null) {}
    }

    class ThirdPartyRequest {
        /**
         * @throws \Biscuit\Exception\ThirdPartyRequestError If the request has already been consumed
         * @throws \Biscuit\Exception\BuilderConsumed If the block builder has already been consumed
         */
        public function createBlock(\Biscuit\Auth\PrivateKey $private_key, \Biscuit\Auth\BlockBuilder $block): \Biscuit\Auth\ThirdPartyBlock {}

        public function __construct() {}
    }

    class ThirdPartyBlock {
        public function __construct() {}
    }

    class Rule {
        /**
         * @param int|string|bool|null $value
         * @throws \Biscuit\Exception\InvalidTerm If the value is invalid
         */
        public function set(string $name, mixed $value): void {}

        /**
         * @throws \Biscuit\Exception\InvalidTerm If the scope parameter is invalid
         */
        public function setScope(string $name, \Biscuit\Auth\PublicKey $key): void {}

        public function __toString(): string {}

        /**
         * @throws \Biscuit\Exception\InvalidRule If the rule syntax is invalid
         * @throws \Biscuit\Exception\InvalidTerm If a parameter value is invalid
         */
        public function __construct(string $source, ?array $params = null, ?array $scope_params = null) {}
    }

    class Fact {
        /**
         * @param int|string|bool|null $value
         * @throws \Biscuit\Exception\InvalidTerm If the value is invalid
         */
        public function set(string $name, mixed $value): void {}

        public function name(): string {}

        public function __toString(): string {}

        /**
         * @throws \Biscuit\Exception\InvalidFact If the fact syntax is invalid
         * @throws \Biscuit\Exception\InvalidTerm If a parameter value is invalid
         */
        public function __construct(string $source, ?array $params = null) {}
    }

    class Check {
        /**
         * @param int|string|bool|null $value
         * @throws \Biscuit\Exception\InvalidTerm If the value is invalid
         */
        public function set(string $name, mixed $value): void {}

        /**
         * @throws \Biscuit\Exception\InvalidTerm If the scope parameter is invalid
         */
        public function setScope(string $name, \Biscuit\Auth\PublicKey $key): void {}

        public function __toString(): string {}

        /**
         * @throws \Biscuit\Exception\InvalidCheck If the check syntax is invalid
         * @throws \Biscuit\Exception\InvalidTerm If a parameter value is invalid
         */
        public function __construct(string $source, ?array $params = null, ?array $scope_params = null) {}
    }

    class Policy {
        /**
         * @param int|string|bool|null $value
         * @throws \Biscuit\Exception\InvalidTerm If the value is invalid
         */
        public function set(string $name, mixed $value): void {}

        /**
         * @throws \Biscuit\Exception\InvalidTerm If the scope parameter is invalid
         */
        public function setScope(string $name, \Biscuit\Auth\PublicKey $key): void {}

        public function __toString(): string {}

        /**
         * @throws \Biscuit\Exception\InvalidPolicy If the policy syntax is invalid
         * @throws \Biscuit\Exception\InvalidTerm If a parameter value is invalid
         */
        public function __construct(string $source, ?array $params = null, ?array $scope_params = null) {}
    }

    class KeyPair {
        public static function fromPrivateKey(\Biscuit\Auth\PrivateKey $private_key): \Biscuit\Auth\KeyPair {}

        public function getPublicKey(): \Biscuit\Auth\PublicKey {}

        public function getPrivateKey(): \Biscuit\Auth\PrivateKey {}

        public function __construct(?\Biscuit\Auth\Algorithm $alg = null) {}
    }

    class PublicKey {
        /**
         * @throws \Biscuit\Exception\InvalidPublicKey If the data is invalid
         */
        public static function fromBytes(string $data, ?\Biscuit\Auth\Algorithm $alg = null): \Biscuit\Auth\PublicKey {}

        /**
         * @throws \Biscuit\Exception\InvalidPublicKey If the PEM is invalid
         */
        public static function fromPem(string $pem): \Biscuit\Auth\PublicKey {}

        /**
         * @throws \Biscuit\Exception\InvalidPublicKey If the DER is invalid
         */
        public static function fromDer(string $der): \Biscuit\Auth\PublicKey {}

        public function toBytes(): array {}

        public function toHex(): string {}

        public function __toString(): string {}

        /**
         * @throws \Biscuit\Exception\InvalidPublicKey If the hex data is invalid
         */
        public function __construct(string $data) {}
    }

    class PrivateKey {
        /**
         * Generates a new random private key with the specified algorithm.
         * Defaults to Ed25519 if no algorithm is specified.
         */
        public static function generate(?\Biscuit\Auth\Algorithm $alg = null): \Biscuit\Auth\PrivateKey {}

        /**
         * @throws \Biscuit\Exception\InvalidPrivateKey If the data is invalid
         */
        public static function fromBytes(string $data, ?\Biscuit\Auth\Algorithm $alg = null): \Biscuit\Auth\PrivateKey {}

        /**
         * @throws \Biscuit\Exception\InvalidPrivateKey If the PEM is invalid
         */
        public static function fromPem(string $pem): \Biscuit\Auth\PrivateKey {}

        /**
         * @throws \Biscuit\Exception\InvalidPrivateKey If the DER is invalid
         */
        public static function fromDer(string $der): \Biscuit\Auth\PrivateKey {}

        /**
         * Returns the public key corresponding to this private key.
         */
        public function getPublicKey(): \Biscuit\Auth\PublicKey {}

        public function toBytes(): array {}

        public function toHex(): string {}

        public function __toString(): string {}

        /**
         * @throws \Biscuit\Exception\InvalidPrivateKey If the hex data is invalid
         */
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
    /**
     * Exception thrown when a private key is invalid or malformed.
     */
    class InvalidPrivateKey extends \Exception {
        public function __construct() {}
    }

    /**
     * Exception thrown when a public key is invalid or malformed.
     */
    class InvalidPublicKey extends \Exception {
        public function __construct() {}
    }

    /**
     * Exception thrown when a check is invalid or malformed.
     */
    class InvalidCheck extends \Exception {
        public function __construct() {}
    }

    /**
     * Exception thrown when a policy is invalid or malformed.
     */
    class InvalidPolicy extends \Exception {
        public function __construct() {}
    }

    /**
     * Exception thrown when a fact is invalid or malformed.
     */
    class InvalidFact extends \Exception {
        public function __construct() {}
    }

    /**
     * Exception thrown when a rule is invalid or malformed.
     */
    class InvalidRule extends \Exception {
        public function __construct() {}
    }

    /**
     * Exception thrown when a term value is invalid or unsupported.
     */
    class InvalidTerm extends \Exception {
        public function __construct() {}
    }

    /**
     * Exception thrown when a third-party request operation fails.
     */
    class ThirdPartyRequestError extends \Exception {
        public function __construct() {}
    }

    /**
     * Exception thrown when authorization fails or encounters an error.
     */
    class AuthorizerError extends \Exception {
        public function __construct() {}
    }

    /**
     * Exception thrown when attempting to use a builder that has already been consumed.
     *
     * Builders are consumed after calling merge() methods.
     * The build() methods clone internally and do not consume the builder.
     */
    class BuilderConsumed extends \Exception {
        public function __construct() {}
    }
}
