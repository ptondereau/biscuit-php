use std::collections::HashMap;
use std::str::FromStr;

use biscuit_auth::builder::Algorithm as BiscuitAlgorithm;
use biscuit_auth::{KeyPair as BiscuitKeyPair, ThirdPartyBlock as BiscuitThirdPartyBlock};
use ext_php_rs::binary_slice::BinarySlice;
use ext_php_rs::zend::{ModuleEntry, ce};
use ext_php_rs::{info_table_end, info_table_row, info_table_start, prelude::*};

/// Algorithm enum for cryptographic key operations
#[php_enum]
#[php(name = "Biscuit\\Auth\\Algorithm")]
pub enum Algorithm {
    #[php(value = 0)]
    Ed25519,
    #[php(name = "Secp256r1", value = 1)]
    Secp256r1,
}

impl From<Algorithm> for BiscuitAlgorithm {
    fn from(alg: Algorithm) -> Self {
        match alg {
            Algorithm::Ed25519 => BiscuitAlgorithm::Ed25519,
            Algorithm::Secp256r1 => BiscuitAlgorithm::Secp256r1,
        }
    }
}

// Enhanced MixedValue to support more term types
#[derive(Debug, ZvalConvert)]
pub enum MixedValue {
    Long(i64),
    Bool(bool),
    ParsedStr(String),
    Bytes(Vec<u8>),
    Array(Vec<MixedValue>),
    None,
}

#[php_class]
#[php(name = "Biscuit\\Auth\\Biscuit")]
pub struct Biscuit(biscuit_auth::Biscuit);

#[php_impl]
impl Biscuit {
    // Static method to create a builder
    #[php(name = "builder")]
    pub fn builder() -> BiscuitBuilder {
        BiscuitBuilder(biscuit_auth::builder::BiscuitBuilder::new())
    }

    // Deserialize from bytes with public key verification
    #[php(name = "fromBytes")]
    pub fn from_bytes(data: BinarySlice<u8>, root: &PublicKey) -> PhpResult<Self> {
        biscuit_auth::Biscuit::from(data.as_ref(), root.0)
            .map(Self)
            .map_err(|e| PhpException::default(format!("Biscuit validation error: {}", e)))
    }

    // Deserialize from base64 with public key verification
    #[php(name = "fromBase64")]
    pub fn from_base64(data: &str, root: &PublicKey) -> PhpResult<Self> {
        biscuit_auth::Biscuit::from_base64(data, root.0)
            .map(Self)
            .map_err(|e| PhpException::default(format!("Biscuit validation error: {}", e)))
    }

    // Serialize to bytes
    pub fn to_bytes(&self) -> PhpResult<Vec<u8>> {
        self.0
            .to_vec()
            .map_err(|e| PhpException::default(format!("Serialization error: {}", e)))
    }

    // Serialize to base64
    pub fn to_base64(&self) -> PhpResult<String> {
        self.0
            .to_base64()
            .map_err(|e| PhpException::default(format!("Serialization error: {}", e)))
    }

    // Get the number of blocks
    pub fn block_count(&self) -> usize {
        self.0.block_count()
    }

    // Print a block's content as Datalog code
    pub fn block_source(&self, index: i64) -> PhpResult<String> {
        self.0
            .print_block_source(index as usize)
            .map_err(|e| PhpException::default(format!("Block error: {}", e)))
    }

    // Append a first-party block
    pub fn append(&self, block: &BlockBuilder) -> PhpResult<Self> {
        self.0
            .append(block.0.clone())
            .map(Self)
            .map_err(|e| PhpException::default(format!("Append error: {}", e)))
    }

    // Append a third-party block
    pub fn append_third_party(
        &self,
        external_key: &PublicKey,
        block: &ThirdPartyBlock,
    ) -> PhpResult<Self> {
        self.0
            .append_third_party(external_key.0, block.0.clone())
            .map(Self)
            .map_err(|e| PhpException::default(format!("Append third party error: {}", e)))
    }

    // Create a third-party request
    pub fn third_party_request(&self) -> PhpResult<ThirdPartyRequest> {
        self.0
            .third_party_request()
            .map(|r| ThirdPartyRequest(Some(r)))
            .map_err(|e| PhpException::default(format!("Third party request error: {}", e)))
    }

    // Get revocation IDs as hex-encoded strings
    pub fn revocation_ids(&self) -> Vec<String> {
        self.0
            .revocation_identifiers()
            .into_iter()
            .map(hex::encode)
            .collect()
    }

    // Get the external key of a block if it exists
    pub fn block_external_key(&self, index: i64) -> PhpResult<Option<PublicKey>> {
        self.0
            .block_external_key(index as usize)
            .map(|opt| opt.map(PublicKey))
            .map_err(|e| PhpException::default(format!("Block error: {}", e)))
    }

    pub fn __to_string(&self) -> String {
        self.0.print()
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\UnverifiedBiscuit")]
pub struct UnverifiedBiscuit(biscuit_auth::UnverifiedBiscuit);

#[php_impl]
impl UnverifiedBiscuit {
    // Deserialize from base64 without verification
    #[php(name = "fromBase64")]
    pub fn from_base64(data: &str) -> PhpResult<Self> {
        biscuit_auth::UnverifiedBiscuit::from_base64(data)
            .map(Self)
            .map_err(|e| PhpException::default(format!("Validation error: {}", e)))
    }

    // Get root key ID
    pub fn root_key_id(&self) -> Option<u32> {
        self.0.root_key_id()
    }

    // Get the number of blocks
    pub fn block_count(&self) -> usize {
        self.0.block_count()
    }

    // Print a block's content as Datalog code
    pub fn block_source(&self, index: i64) -> PhpResult<String> {
        self.0
            .print_block_source(index as usize)
            .map_err(|e| PhpException::default(format!("Block error: {}", e)))
    }

    // Append a block
    pub fn append(&self, block: &BlockBuilder) -> PhpResult<Self> {
        self.0
            .append(block.0.clone())
            .map(Self)
            .map_err(|e| PhpException::default(format!("Append error: {}", e)))
    }

    // Get revocation IDs
    pub fn revocation_ids(&self) -> Vec<String> {
        self.0
            .revocation_identifiers()
            .into_iter()
            .map(hex::encode)
            .collect()
    }

    // Verify with public key
    pub fn verify(&self, root: &PublicKey) -> PhpResult<Biscuit> {
        self.0
            .clone()
            .verify(root.0)
            .map(Biscuit)
            .map_err(|e| PhpException::default(format!("Verification error: {}", e)))
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\Authorizer")]
pub struct Authorizer(biscuit_auth::Authorizer);

#[php_impl]
impl Authorizer {
    // Run authorization checks
    pub fn authorize(&mut self) -> PhpResult<usize> {
        self.0
            .authorize()
            .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))
    }

    // Query for facts
    pub fn query(&mut self, rule: &Rule) -> PhpResult<Vec<Fact>> {
        let facts: Result<Vec<biscuit_auth::builder::Fact>, _> = self.0.query(rule.0.clone());
        facts
            .map(|f| f.iter().map(|fact| Fact(fact.clone())).collect())
            .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))
    }

    // Serialize to base64 snapshot
    pub fn base64_snapshot(&self) -> PhpResult<String> {
        self.0
            .to_base64_snapshot()
            .map_err(|e| PhpException::default(format!("Serialization error: {}", e)))
    }

    // Serialize to raw snapshot
    pub fn raw_snapshot(&self) -> PhpResult<Vec<u8>> {
        self.0
            .to_raw_snapshot()
            .map_err(|e| PhpException::default(format!("Serialization error: {}", e)))
    }

    // Deserialize from base64 snapshot
    #[php(name = "fromBase64Snapshot")]
    pub fn from_base64_snapshot(input: &str) -> PhpResult<Self> {
        biscuit_auth::Authorizer::from_base64_snapshot(input)
            .map(Self)
            .map_err(|e| PhpException::default(format!("Validation error: {}", e)))
    }

    // Deserialize from raw snapshot
    #[php(name = "fromRawSnapshot")]
    pub fn from_raw_snapshot(input: BinarySlice<u8>) -> PhpResult<Self> {
        biscuit_auth::Authorizer::from_raw_snapshot(input.as_ref())
            .map(Self)
            .map_err(|e| PhpException::default(format!("Validation error: {}", e)))
    }

    pub fn __to_string(&self) -> String {
        self.0.to_string()
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\AuthorizerBuilder")]
pub struct AuthorizerBuilder(biscuit_auth::AuthorizerBuilder);

#[php_impl]
impl AuthorizerBuilder {
    pub fn __construct() -> Self {
        Self(biscuit_auth::AuthorizerBuilder::new())
    }

    pub fn add_code(&mut self, source: &str) -> PhpResult<()> {
        self.0 = self
            .0
            .clone()
            .code(source)
            .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))?;
        Ok(())
    }

    pub fn add_code_with_params(
        &mut self,
        source: &str,
        params: HashMap<String, MixedValue>,
        scope_params: HashMap<String, &PublicKey>,
    ) -> PhpResult<()> {
        let mut term_params: HashMap<String, biscuit_auth::builder::Term> =
            HashMap::with_capacity(params.len());

        for (key, p) in params.iter() {
            let term_value = mixed_value_to_term(p)?;
            term_params.insert(key.clone(), term_value);
        }

        let mut scope_params_cloned: HashMap<String, biscuit_auth::PublicKey> =
            HashMap::with_capacity(scope_params.len());

        for (key, scope_param) in scope_params.iter() {
            scope_params_cloned.insert(key.clone(), scope_param.0);
        }

        self.0 = self
            .0
            .clone()
            .code_with_params(source, term_params, scope_params_cloned)
            .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))?;
        Ok(())
    }

    pub fn add_fact(&mut self, fact: &Fact) -> PhpResult<()> {
        self.0 = self
            .0
            .clone()
            .fact(fact.0.clone())
            .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))?;
        Ok(())
    }

    pub fn add_rule(&mut self, rule: &Rule) -> PhpResult<()> {
        self.0 = self
            .0
            .clone()
            .rule(rule.0.clone())
            .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))?;
        Ok(())
    }

    pub fn add_check(&mut self, check: &Check) -> PhpResult<()> {
        self.0 = self
            .0
            .clone()
            .check(check.0.clone())
            .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))?;
        Ok(())
    }

    pub fn add_policy(&mut self, policy: &Policy) -> PhpResult<()> {
        self.0 = self
            .0
            .clone()
            .policy(policy.0.clone())
            .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))?;
        Ok(())
    }

    pub fn set_time(&mut self) {
        self.0 = self.0.clone().time();
    }

    pub fn merge(&mut self, other: &AuthorizerBuilder) {
        self.0 = self.0.clone().merge(other.0.clone());
    }

    pub fn merge_block(&mut self, block: &BlockBuilder) {
        self.0 = self.0.clone().merge_block(block.0.clone());
    }

    // Serialize to base64 snapshot
    pub fn base64_snapshot(&self) -> PhpResult<String> {
        self.0
            .clone()
            .to_base64_snapshot()
            .map_err(|e| PhpException::default(format!("Serialization error: {}", e)))
    }

    // Serialize to raw snapshot
    pub fn raw_snapshot(&self) -> PhpResult<Vec<u8>> {
        self.0
            .clone()
            .to_raw_snapshot()
            .map_err(|e| PhpException::default(format!("Serialization error: {}", e)))
    }

    // Deserialize from base64 snapshot
    #[php(name = "fromBase64Snapshot")]
    pub fn from_base64_snapshot(input: &str) -> PhpResult<Self> {
        biscuit_auth::AuthorizerBuilder::from_base64_snapshot(input)
            .map(Self)
            .map_err(|e| PhpException::default(format!("Validation error: {}", e)))
    }

    // Deserialize from raw snapshot
    #[php(name = "fromRawSnapshot")]
    pub fn from_raw_snapshot(input: BinarySlice<u8>) -> PhpResult<Self> {
        biscuit_auth::AuthorizerBuilder::from_raw_snapshot(input.as_ref())
            .map(Self)
            .map_err(|e| PhpException::default(format!("Validation error: {}", e)))
    }

    // Build with token
    pub fn build(&self, token: &Biscuit) -> PhpResult<Authorizer> {
        self.0
            .clone()
            .build(&token.0)
            .map(Authorizer)
            .map_err(|e| PhpException::default(format!("Build error: {}", e)))
    }

    // Build without token
    pub fn build_unauthenticated(&self) -> PhpResult<Authorizer> {
        self.0
            .clone()
            .build_unauthenticated()
            .map(Authorizer)
            .map_err(|e| PhpException::default(format!("Build error: {}", e)))
    }

    pub fn __to_string(&self) -> String {
        self.0.to_string()
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\BiscuitBuilder")]
pub struct BiscuitBuilder(biscuit_auth::builder::BiscuitBuilder);

#[php_impl]
impl BiscuitBuilder {
    pub fn __construct() -> Self {
        Self(biscuit_auth::builder::BiscuitBuilder::new())
    }

    // Build the biscuit with a private key
    pub fn build(&self, root: &PrivateKey) -> PhpResult<Biscuit> {
        let keypair = BiscuitKeyPair::from(&root.0);
        self.0
            .clone()
            .build(&keypair)
            .map(Biscuit)
            .map_err(|e| PhpException::default(format!("Build error: {}", e)))
    }

    pub fn add_code(&mut self, source: &str) -> PhpResult<()> {
        self.0 = self
            .0
            .clone()
            .code(source)
            .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))?;
        Ok(())
    }

    pub fn add_code_with_params(
        &mut self,
        source: &str,
        params: HashMap<String, MixedValue>,
        scope_params: HashMap<String, &PublicKey>,
    ) -> PhpResult<()> {
        let mut term_params: HashMap<String, biscuit_auth::builder::Term> =
            HashMap::with_capacity(params.len());

        for (key, p) in params.iter() {
            let term_value = mixed_value_to_term(p)?;
            term_params.insert(key.clone(), term_value);
        }

        let mut scope_params_cloned: HashMap<String, biscuit_auth::PublicKey> =
            HashMap::with_capacity(scope_params.len());

        for (key, scope_param) in scope_params.iter() {
            scope_params_cloned.insert(key.clone(), scope_param.0);
        }

        self.0 = self
            .0
            .clone()
            .code_with_params(source, term_params, scope_params_cloned)
            .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))?;
        Ok(())
    }

    pub fn merge(&mut self, other: &BlockBuilder) {
        self.0 = self.0.clone().merge(other.0.clone());
    }

    pub fn add_fact(&mut self, fact: &Fact) -> PhpResult<()> {
        self.0 = self
            .0
            .clone()
            .fact(fact.0.clone())
            .map_err(|e| PhpException::from_class::<InvalidFact>(e.to_string()))?;
        Ok(())
    }

    pub fn add_rule(&mut self, rule: &Rule) -> PhpResult<()> {
        self.0 = self
            .0
            .clone()
            .rule(rule.0.clone())
            .map_err(|e| PhpException::from_class::<InvalidRule>(e.to_string()))?;
        Ok(())
    }

    pub fn add_check(&mut self, check: &Check) -> PhpResult<()> {
        self.0 = self
            .0
            .clone()
            .check(check.0.clone())
            .map_err(|e| PhpException::from_class::<InvalidCheck>(e.to_string()))?;
        Ok(())
    }

    pub fn set_root_key_id(&mut self, root_key_id: u32) {
        self.0 = self.0.clone().root_key_id(root_key_id);
    }

    pub fn __to_string(&self) -> String {
        self.0.to_string()
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\BlockBuilder")]
#[derive(Debug, Clone)]
pub struct BlockBuilder(biscuit_auth::builder::BlockBuilder);

#[php_impl]
impl BlockBuilder {
    pub fn __construct() -> Self {
        Self(biscuit_auth::builder::BlockBuilder::default())
    }

    pub fn add_fact(&mut self, fact: &Fact) -> PhpResult<()> {
        self.0 = self
            .0
            .clone()
            .fact(fact.0.clone())
            .map_err(|e| PhpException::from_class::<InvalidFact>(e.to_string()))?;
        Ok(())
    }

    pub fn add_rule(&mut self, rule: &Rule) -> PhpResult<()> {
        self.0 = self
            .0
            .clone()
            .rule(rule.0.clone())
            .map_err(|e| PhpException::from_class::<InvalidRule>(e.to_string()))?;
        Ok(())
    }

    pub fn add_check(&mut self, check: &Check) -> PhpResult<()> {
        self.0 = self
            .0
            .clone()
            .check(check.0.clone())
            .map_err(|e| PhpException::from_class::<InvalidCheck>(e.to_string()))?;
        Ok(())
    }

    pub fn add_code(&mut self, source: &str) -> PhpResult<()> {
        self.0 = self
            .0
            .clone()
            .code(source)
            .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))?;
        Ok(())
    }

    pub fn add_code_with_params(
        &mut self,
        source: &str,
        params: HashMap<String, MixedValue>,
        scope_params: HashMap<String, &PublicKey>,
    ) -> PhpResult<()> {
        let mut term_params: HashMap<String, biscuit_auth::builder::Term> =
            HashMap::with_capacity(params.len());

        for (key, p) in params.iter() {
            let term_value = mixed_value_to_term(p)?;
            term_params.insert(key.clone(), term_value);
        }

        let mut scope_params_cloned: HashMap<String, biscuit_auth::PublicKey> =
            HashMap::with_capacity(scope_params.len());

        for (key, scope_param) in scope_params.iter() {
            scope_params_cloned.insert(key.clone(), scope_param.0);
        }

        self.0 = self
            .0
            .clone()
            .code_with_params(source, term_params, scope_params_cloned)
            .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))?;
        Ok(())
    }

    pub fn merge(&mut self, other: &BlockBuilder) {
        self.0 = self.0.clone().merge(other.0.clone());
    }

    pub fn __to_string(&self) -> String {
        format!("{}", self.0)
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\ThirdPartyRequest")]
pub struct ThirdPartyRequest(Option<biscuit_auth::ThirdPartyRequest>);

#[php_impl]
impl ThirdPartyRequest {
    // Create a third-party block - consumes the request
    pub fn create_block(
        &mut self,
        private_key: &PrivateKey,
        block: &BlockBuilder,
    ) -> PhpResult<ThirdPartyBlock> {
        let request = self.0.take().ok_or_else(|| {
            PhpException::from_class::<ThirdPartyRequestError>(
                "ThirdPartyRequest already consumed".to_string(),
            )
        })?;

        request
            .create_block(&private_key.0, block.0.clone())
            .map(ThirdPartyBlock)
            .map_err(|e| PhpException::from_class::<ThirdPartyRequestError>(e.to_string()))
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\ThirdPartyBlock")]
#[derive(Clone)]
pub struct ThirdPartyBlock(BiscuitThirdPartyBlock);

#[php_class]
#[php(name = "Biscuit\\Auth\\Rule")]
#[derive(Debug, Clone)]
pub struct Rule(biscuit_auth::builder::Rule);

#[php_impl]
impl Rule {
    pub fn __construct(source: &str) -> PhpResult<Self> {
        source
            .try_into()
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidRule>(e.to_string()))
    }

    /// @param int|string|bool|null $value
    pub fn set(&mut self, name: &str, value: MixedValue) -> PhpResult<()> {
        let term_value = mixed_value_to_term(&value)?;

        self.0
            .set(name, term_value)
            .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
    }

    pub fn set_scope(&mut self, name: &str, key: &PublicKey) -> PhpResult<()> {
        self.0
            .set_scope(name, key.0)
            .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
    }

    pub fn __to_string(&self) -> String {
        format!("{}", self.0)
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\Fact")]
#[derive(Debug, Clone)]
pub struct Fact(biscuit_auth::builder::Fact);

#[php_impl]
impl Fact {
    pub fn __construct(source: &str) -> PhpResult<Self> {
        source
            .try_into()
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidFact>(e.to_string()))
    }

    /// @param int|string|bool|null $value
    pub fn set(&mut self, name: &str, value: MixedValue) -> PhpResult<()> {
        let term_value = mixed_value_to_term(&value)?;

        self.0
            .set(name, term_value)
            .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
    }

    // Get fact name
    pub fn name(&self) -> String {
        self.0.predicate.name.clone()
    }

    pub fn __to_string(&self) -> String {
        format!("{}", self.0)
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\Check")]
#[derive(Debug, Clone)]
pub struct Check(biscuit_auth::builder::Check);

#[php_impl]
impl Check {
    pub fn __construct(source: &str) -> PhpResult<Self> {
        source
            .try_into()
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidCheck>(e.to_string()))
    }

    /// @param int|string|bool|null $value
    pub fn set(&mut self, name: &str, value: MixedValue) -> PhpResult<()> {
        let term_value = mixed_value_to_term(&value)?;

        self.0
            .set(name, term_value)
            .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
    }

    pub fn set_scope(&mut self, name: &str, key: &PublicKey) -> PhpResult<()> {
        self.0
            .set_scope(name, key.0)
            .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
    }

    pub fn __to_string(&self) -> String {
        format!("{}", self.0)
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\Policy")]
#[derive(Debug, Clone)]
pub struct Policy(biscuit_auth::builder::Policy);

#[php_impl]
impl Policy {
    pub fn __construct(source: &str) -> PhpResult<Self> {
        source
            .try_into()
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPolicy>(e.to_string()))
    }

    /// @param int|string|bool|null $value
    pub fn set(&mut self, name: &str, value: MixedValue) -> PhpResult<()> {
        let term_value = mixed_value_to_term(&value)?;

        self.0
            .set(name, term_value)
            .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
    }

    pub fn set_scope(&mut self, name: &str, key: &PublicKey) -> PhpResult<()> {
        self.0
            .set_scope(name, key.0)
            .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
    }

    pub fn __to_string(&self) -> String {
        format!("{}", self.0)
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\KeyPair")]
#[derive(Debug)]
pub struct KeyPair(BiscuitKeyPair);

#[php_impl]
impl KeyPair {
    pub fn __construct() -> Self {
        Self(BiscuitKeyPair::new())
    }

    #[php(name = "newWithAlgorithm")]
    pub fn new_with_algorithm(alg: Option<Algorithm>) -> Self {
        let algorithm = alg.unwrap_or(Algorithm::Ed25519).into();
        Self(BiscuitKeyPair::new_with_algorithm(algorithm))
    }

    #[php(name = "fromPrivateKey")]
    pub fn from_private_key(private_key: &PrivateKey) -> Self {
        Self(BiscuitKeyPair::from(&private_key.0))
    }

    pub fn public(&self) -> PublicKey {
        PublicKey(self.0.public())
    }

    pub fn private(&self) -> PrivateKey {
        PrivateKey(self.0.private())
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\PublicKey")]
#[derive(Debug, Clone, Copy)]
pub struct PublicKey(biscuit_auth::PublicKey);

#[php_impl]
impl PublicKey {
    pub fn __construct(data: &str) -> PhpResult<Self> {
        biscuit_auth::PublicKey::from_str(data)
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPublicKey>(e.to_string()))
    }

    #[php(name = "fromBytes")]
    pub fn from_bytes(data: BinarySlice<u8>, alg: Option<Algorithm>) -> PhpResult<Self> {
        let algorithm = alg.unwrap_or(Algorithm::Ed25519).into();
        biscuit_auth::PublicKey::from_bytes(data.as_ref(), algorithm)
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPublicKey>(e.to_string()))
    }

    #[php(name = "fromPem")]
    pub fn from_pem(pem: &str) -> PhpResult<Self> {
        biscuit_auth::PublicKey::from_pem(pem)
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPublicKey>(e.to_string()))
    }

    #[php(name = "fromDer")]
    pub fn from_der(der: BinarySlice<u8>) -> PhpResult<Self> {
        biscuit_auth::PublicKey::from_der(der.as_ref())
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPublicKey>(e.to_string()))
    }

    pub fn to_bytes(&self) -> Vec<u8> {
        self.0.to_bytes()
    }

    pub fn to_hex(&self) -> String {
        self.0.to_string()
    }

    pub fn __to_string(&self) -> String {
        self.0.to_string()
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\PrivateKey")]
#[derive(Debug, Clone)]
pub struct PrivateKey(biscuit_auth::PrivateKey);

#[php_impl]
impl PrivateKey {
    pub fn __construct(data: &str) -> PhpResult<Self> {
        biscuit_auth::PrivateKey::from_str(data)
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPrivateKey>(e.to_string()))
    }

    #[php(name = "fromBytes")]
    pub fn from_bytes(data: BinarySlice<u8>, alg: Option<Algorithm>) -> PhpResult<Self> {
        let algorithm = alg.unwrap_or(Algorithm::Ed25519).into();
        biscuit_auth::PrivateKey::from_bytes(data.as_ref(), algorithm)
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPrivateKey>(e.to_string()))
    }

    #[php(name = "fromPem")]
    pub fn from_pem(pem: &str) -> PhpResult<Self> {
        biscuit_auth::PrivateKey::from_pem(pem)
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPrivateKey>(e.to_string()))
    }

    #[php(name = "fromDer")]
    pub fn from_der(der: BinarySlice<u8>) -> PhpResult<Self> {
        biscuit_auth::PrivateKey::from_der(der.as_ref())
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPrivateKey>(e.to_string()))
    }

    pub fn to_bytes(&self) -> Vec<u8> {
        self.0.to_bytes().to_vec()
    }

    pub fn to_hex(&self) -> String {
        self.0.to_prefixed_string()
    }

    pub fn __to_string(&self) -> String {
        self.0.to_prefixed_string()
    }
}

fn mixed_value_to_term(value: &MixedValue) -> PhpResult<biscuit_auth::builder::Term> {
    match value {
        MixedValue::Long(v) => Ok(biscuit_auth::builder::Term::Integer(*v)),
        MixedValue::Bool(b) => Ok(biscuit_auth::builder::Term::Bool(*b)),
        MixedValue::ParsedStr(s) => Ok(biscuit_auth::builder::Term::Str(s.clone())),
        MixedValue::Bytes(b) => Ok(biscuit_auth::builder::Term::Bytes(b.clone())),
        MixedValue::Array(arr) => {
            // Recursively convert array elements to terms
            let terms: Result<Vec<_>, _> = arr.iter().map(mixed_value_to_term).collect();
            let term_set: std::collections::BTreeSet<_> = terms?.into_iter().collect();
            Ok(biscuit_auth::builder::Term::Set(term_set))
        }
        MixedValue::None => Err(PhpException::from_class::<InvalidTerm>(
            "unexpected value".to_string(),
        )),
    }
}

// Exception classes as proper PHP classes
#[php_class]
#[php(name = "Biscuit\\Exception\\InvalidPrivateKey")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default)]
pub struct InvalidPrivateKey;

#[php_class]
#[php(name = "Biscuit\\Exception\\InvalidPublicKey")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default)]
pub struct InvalidPublicKey;

#[php_class]
#[php(name = "Biscuit\\Exception\\InvalidCheck")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default)]
pub struct InvalidCheck;

#[php_class]
#[php(name = "Biscuit\\Exception\\InvalidPolicy")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default)]
pub struct InvalidPolicy;

#[php_class]
#[php(name = "Biscuit\\Exception\\InvalidFact")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default)]
pub struct InvalidFact;

#[php_class]
#[php(name = "Biscuit\\Exception\\InvalidRule")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default)]
pub struct InvalidRule;

#[php_class]
#[php(name = "Biscuit\\Exception\\InvalidTerm")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default)]
pub struct InvalidTerm;

#[php_class]
#[php(name = "Biscuit\\Exception\\ThirdPartyRequestError")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default)]
pub struct ThirdPartyRequestError;

#[php_class]
#[php(name = "Biscuit\\Exception\\AuthorizerError")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default)]
pub struct AuthorizerError;

pub fn startup(_ty: i32, _mod_num: i32) -> i32 {
    0
}

pub extern "C" fn php_module_info(_module: *mut ModuleEntry) {
    info_table_start!();
    info_table_row!("ext-biscuit-php", "enabled");
    info_table_row!("biscuit-auth version", "6.0.0");
    info_table_end!();
}

#[php_module]
#[php(startup = "startup")]
pub fn get_module(module: ModuleBuilder) -> ModuleBuilder {
    module
        .info_function(php_module_info)
        .enumeration::<Algorithm>()
        .class::<Biscuit>()
        .class::<UnverifiedBiscuit>()
        .class::<Authorizer>()
        .class::<AuthorizerBuilder>()
        .class::<BiscuitBuilder>()
        .class::<BlockBuilder>()
        .class::<ThirdPartyRequest>()
        .class::<ThirdPartyBlock>()
        .class::<Rule>()
        .class::<Fact>()
        .class::<Check>()
        .class::<Policy>()
        .class::<KeyPair>()
        .class::<PublicKey>()
        .class::<PrivateKey>()
        .class::<InvalidPrivateKey>()
        .class::<InvalidPublicKey>()
        .class::<InvalidCheck>()
        .class::<InvalidPolicy>()
        .class::<InvalidFact>()
        .class::<InvalidRule>()
        .class::<InvalidTerm>()
        .class::<ThirdPartyRequestError>()
        .class::<AuthorizerError>()
}
