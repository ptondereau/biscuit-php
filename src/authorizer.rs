use std::collections::HashMap;

use ext_php_rs::binary_slice::BinarySlice;
use ext_php_rs::prelude::*;

use crate::biscuit::Biscuit;
use crate::builders::BlockBuilder;
use crate::datalog::{Check, Fact, Policy, Rule};
use crate::errors::AuthorizerError;
use crate::helpers::{MixedValue, get_builder, mixed_value_to_term, take_builder};
use crate::keys::PublicKey;

#[php_class]
#[php(name = "Biscuit\\Auth\\Authorizer")]
#[derive(Clone)]
pub struct Authorizer(biscuit_auth::Authorizer);

#[php_impl]
impl Authorizer {
    pub fn authorize(&mut self) -> PhpResult<usize> {
        self.0
            .authorize()
            .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))
    }

    pub fn query(&mut self, rule: &Rule) -> PhpResult<Vec<Fact>> {
        let facts: Result<Vec<biscuit_auth::builder::Fact>, _> = self.0.query(rule.0.clone());
        facts
            .map(|f| f.iter().map(|fact| Fact(fact.clone())).collect())
            .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))
    }

    pub fn base64_snapshot(&self) -> PhpResult<String> {
        self.0
            .to_base64_snapshot()
            .map_err(|e| PhpException::default(format!("Serialization error: {}", e)))
    }

    pub fn raw_snapshot(&self) -> PhpResult<Vec<u8>> {
        self.0
            .to_raw_snapshot()
            .map_err(|e| PhpException::default(format!("Serialization error: {}", e)))
    }

    #[php(name = "fromBase64Snapshot")]
    pub fn from_base64_snapshot(input: &str) -> PhpResult<Self> {
        biscuit_auth::Authorizer::from_base64_snapshot(input)
            .map(Self)
            .map_err(|e| PhpException::default(format!("Validation error: {}", e)))
    }

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
#[derive(Clone)]
pub struct AuthorizerBuilder(Option<biscuit_auth::AuthorizerBuilder>);

#[php_impl]
impl AuthorizerBuilder {
    pub fn __construct(
        source: Option<String>,
        params: Option<HashMap<String, MixedValue>>,
        scope_params: Option<HashMap<String, &PublicKey>>,
    ) -> PhpResult<Self> {
        let mut builder = Self(Some(biscuit_auth::AuthorizerBuilder::new()));
        if let Some(src) = source {
            builder.add_code(&src, params, scope_params)?;
        }
        Ok(builder)
    }

    pub fn add_code(
        &mut self,
        source: &str,
        params: Option<HashMap<String, MixedValue>>,
        scope_params: Option<HashMap<String, &PublicKey>>,
    ) -> PhpResult<()> {
        let term_params: HashMap<String, biscuit_auth::builder::Term> = match params {
            Some(p) => p
                .iter()
                .map(|(k, v)| mixed_value_to_term(v).map(|term| (k.clone(), term)))
                .collect::<Result<HashMap<String, biscuit_auth::builder::Term>, PhpException>>()?,
            None => HashMap::new(),
        };

        let scope: HashMap<String, biscuit_auth::PublicKey> = match scope_params {
            Some(sp) => sp.iter().map(|(k, v)| (k.clone(), v.0)).collect(),
            None => HashMap::new(),
        };

        self.0 = Some(
            take_builder(&mut self.0)?
                .code_with_params(source, term_params, scope)
                .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))?,
        );
        Ok(())
    }

    pub fn add_fact(&mut self, fact: &Fact) -> PhpResult<()> {
        self.0 = Some(
            take_builder(&mut self.0)?
                .fact(fact.0.clone())
                .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))?,
        );
        Ok(())
    }

    pub fn add_rule(&mut self, rule: &Rule) -> PhpResult<()> {
        self.0 = Some(
            take_builder(&mut self.0)?
                .rule(rule.0.clone())
                .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))?,
        );
        Ok(())
    }

    pub fn add_check(&mut self, check: &Check) -> PhpResult<()> {
        self.0 = Some(
            take_builder(&mut self.0)?
                .check(check.0.clone())
                .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))?,
        );
        Ok(())
    }

    pub fn add_policy(&mut self, policy: &Policy) -> PhpResult<()> {
        self.0 = Some(
            take_builder(&mut self.0)?
                .policy(policy.0.clone())
                .map_err(|e| PhpException::from_class::<AuthorizerError>(e.to_string()))?,
        );
        Ok(())
    }

    pub fn set_time(&mut self) -> PhpResult<()> {
        self.0 = Some(take_builder(&mut self.0)?.time());
        Ok(())
    }

    pub fn merge(&mut self, other: &mut AuthorizerBuilder) -> PhpResult<()> {
        self.0 = Some(take_builder(&mut self.0)?.merge(take_builder(&mut other.0)?));
        Ok(())
    }

    pub fn merge_block(&mut self, block: &mut BlockBuilder) -> PhpResult<()> {
        self.0 = Some(take_builder(&mut self.0)?.merge_block(take_builder(&mut block.0)?));
        Ok(())
    }

    pub fn base64_snapshot(&self) -> PhpResult<String> {
        get_builder(&self.0)?
            .clone()
            .to_base64_snapshot()
            .map_err(|e| PhpException::default(format!("Serialization error: {}", e)))
    }

    pub fn raw_snapshot(&self) -> PhpResult<Vec<u8>> {
        get_builder(&self.0)?
            .clone()
            .to_raw_snapshot()
            .map_err(|e| PhpException::default(format!("Serialization error: {}", e)))
    }

    #[php(name = "fromBase64Snapshot")]
    pub fn from_base64_snapshot(input: &str) -> PhpResult<Self> {
        biscuit_auth::AuthorizerBuilder::from_base64_snapshot(input)
            .map(|b| Self(Some(b)))
            .map_err(|e| PhpException::default(format!("Validation error: {}", e)))
    }

    #[php(name = "fromRawSnapshot")]
    pub fn from_raw_snapshot(input: BinarySlice<u8>) -> PhpResult<Self> {
        biscuit_auth::AuthorizerBuilder::from_raw_snapshot(input.as_ref())
            .map(|b| Self(Some(b)))
            .map_err(|e| PhpException::default(format!("Validation error: {}", e)))
    }

    pub fn build(&self, token: &Biscuit) -> PhpResult<Authorizer> {
        get_builder(&self.0)?
            .clone()
            .build(&token.0)
            .map(Authorizer)
            .map_err(|e| PhpException::default(format!("Build error: {}", e)))
    }

    pub fn build_unauthenticated(&self) -> PhpResult<Authorizer> {
        get_builder(&self.0)?
            .clone()
            .build_unauthenticated()
            .map(Authorizer)
            .map_err(|e| PhpException::default(format!("Build error: {}", e)))
    }

    pub fn __to_string(&self) -> PhpResult<String> {
        Ok(get_builder(&self.0)?.to_string())
    }
}
