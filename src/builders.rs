use std::collections::HashMap;

use biscuit_auth::KeyPair as BiscuitKeyPair;
use ext_php_rs::prelude::*;

use crate::biscuit::Biscuit;
use crate::datalog::{Check, Fact, Rule};
use crate::errors::{InvalidCheck, InvalidFact, InvalidRule, InvalidTerm};
use crate::helpers::{MixedValue, get_builder, mixed_value_to_term, take_builder};
use crate::keys::{PrivateKey, PublicKey};

#[php_class]
#[php(name = "Biscuit\\Auth\\BiscuitBuilder")]
#[derive(Clone)]
pub struct BiscuitBuilder(pub(crate) Option<biscuit_auth::builder::BiscuitBuilder>);

impl Default for BiscuitBuilder {
    fn default() -> Self {
        Self(Some(biscuit_auth::builder::BiscuitBuilder::new()))
    }
}

impl BiscuitBuilder {
    pub fn new() -> Self {
        Self::default()
    }
}

#[php_impl]
impl BiscuitBuilder {
    pub fn __construct(
        source: Option<String>,
        params: Option<HashMap<String, MixedValue>>,
        scope_params: Option<HashMap<String, &PublicKey>>,
    ) -> PhpResult<Self> {
        let mut builder = Self(Some(biscuit_auth::builder::BiscuitBuilder::new()));
        if let Some(src) = source {
            builder.add_code(&src, params, scope_params)?;
        }
        Ok(builder)
    }

    pub fn build(&self, root: &PrivateKey) -> PhpResult<Biscuit> {
        let keypair = BiscuitKeyPair::from(&root.0);
        get_builder(&self.0)?
            .clone()
            .build(&keypair)
            .map(Biscuit::wrap)
            .map_err(|e| PhpException::default(format!("Build error: {}", e)))
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
                .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))?,
        );
        Ok(())
    }

    pub fn merge(&mut self, other: &mut BlockBuilder) -> PhpResult<()> {
        self.0 = Some(take_builder(&mut self.0)?.merge(take_builder(&mut other.0)?));
        Ok(())
    }

    pub fn add_fact(&mut self, fact: &Fact) -> PhpResult<()> {
        self.0 = Some(
            take_builder(&mut self.0)?
                .fact(fact.0.clone())
                .map_err(|e| PhpException::from_class::<InvalidFact>(e.to_string()))?,
        );
        Ok(())
    }

    pub fn add_rule(&mut self, rule: &Rule) -> PhpResult<()> {
        self.0 = Some(
            take_builder(&mut self.0)?
                .rule(rule.0.clone())
                .map_err(|e| PhpException::from_class::<InvalidRule>(e.to_string()))?,
        );
        Ok(())
    }

    pub fn add_check(&mut self, check: &Check) -> PhpResult<()> {
        self.0 = Some(
            take_builder(&mut self.0)?
                .check(check.0.clone())
                .map_err(|e| PhpException::from_class::<InvalidCheck>(e.to_string()))?,
        );
        Ok(())
    }

    pub fn set_root_key_id(&mut self, root_key_id: u32) -> PhpResult<()> {
        self.0 = Some(take_builder(&mut self.0)?.root_key_id(root_key_id));
        Ok(())
    }

    pub fn __to_string(&self) -> PhpResult<String> {
        Ok(get_builder(&self.0)?.to_string())
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\BlockBuilder")]
#[derive(Debug, Clone)]
pub struct BlockBuilder(pub(crate) Option<biscuit_auth::builder::BlockBuilder>);

#[php_impl]
impl BlockBuilder {
    pub fn __construct(
        source: Option<String>,
        params: Option<HashMap<String, MixedValue>>,
        scope_params: Option<HashMap<String, &PublicKey>>,
    ) -> PhpResult<Self> {
        let mut builder = Self(Some(biscuit_auth::builder::BlockBuilder::default()));
        if let Some(src) = source {
            builder.add_code(&src, params, scope_params)?;
        }
        Ok(builder)
    }

    pub fn add_fact(&mut self, fact: &Fact) -> PhpResult<()> {
        self.0 = Some(
            take_builder(&mut self.0)?
                .fact(fact.0.clone())
                .map_err(|e| PhpException::from_class::<InvalidFact>(e.to_string()))?,
        );
        Ok(())
    }

    pub fn add_rule(&mut self, rule: &Rule) -> PhpResult<()> {
        self.0 = Some(
            take_builder(&mut self.0)?
                .rule(rule.0.clone())
                .map_err(|e| PhpException::from_class::<InvalidRule>(e.to_string()))?,
        );
        Ok(())
    }

    pub fn add_check(&mut self, check: &Check) -> PhpResult<()> {
        self.0 = Some(
            take_builder(&mut self.0)?
                .check(check.0.clone())
                .map_err(|e| PhpException::from_class::<InvalidCheck>(e.to_string()))?,
        );
        Ok(())
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
                .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))?,
        );
        Ok(())
    }

    pub fn merge(&mut self, other: &mut BlockBuilder) -> PhpResult<()> {
        self.0 = Some(take_builder(&mut self.0)?.merge(take_builder(&mut other.0)?));
        Ok(())
    }

    pub fn __to_string(&self) -> PhpResult<String> {
        Ok(format!("{}", get_builder(&self.0)?))
    }
}
