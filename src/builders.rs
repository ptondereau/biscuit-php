use std::collections::HashMap;

use biscuit_auth::KeyPair as BiscuitKeyPair;
use ext_php_rs::prelude::*;

use crate::biscuit::Biscuit;
use crate::datalog::{Check, Fact, Rule};
use crate::errors::{BuildKind, DatalogKind, ResultExt};
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
        let token = get_builder(&self.0)?
            .clone()
            .build(&keypair)
            .build(BuildKind::Token)?;
        Ok(Biscuit::wrap(token))
    }

    pub fn add_code(
        &mut self,
        source: &str,
        params: Option<HashMap<String, MixedValue>>,
        scope_params: Option<HashMap<String, &PublicKey>>,
    ) -> PhpResult<()> {
        let term_params = collect_term_params(params)?;
        let scope = collect_scope_params(scope_params);

        let next = take_builder(&mut self.0)?
            .code_with_params(source, term_params, scope)
            .datalog(DatalogKind::Term)?;
        self.0 = Some(next);
        Ok(())
    }

    pub fn merge(&mut self, other: &mut BlockBuilder) -> PhpResult<()> {
        self.0 = Some(take_builder(&mut self.0)?.merge(take_builder(&mut other.0)?));
        Ok(())
    }

    pub fn add_fact(&mut self, fact: &Fact) -> PhpResult<()> {
        let next = take_builder(&mut self.0)?
            .fact(fact.0.clone())
            .datalog(DatalogKind::Fact)?;
        self.0 = Some(next);
        Ok(())
    }

    pub fn add_rule(&mut self, rule: &Rule) -> PhpResult<()> {
        let next = take_builder(&mut self.0)?
            .rule(rule.0.clone())
            .datalog(DatalogKind::Rule)?;
        self.0 = Some(next);
        Ok(())
    }

    pub fn add_check(&mut self, check: &Check) -> PhpResult<()> {
        let next = take_builder(&mut self.0)?
            .check(check.0.clone())
            .datalog(DatalogKind::Check)?;
        self.0 = Some(next);
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
        let next = take_builder(&mut self.0)?
            .fact(fact.0.clone())
            .datalog(DatalogKind::Fact)?;
        self.0 = Some(next);
        Ok(())
    }

    pub fn add_rule(&mut self, rule: &Rule) -> PhpResult<()> {
        let next = take_builder(&mut self.0)?
            .rule(rule.0.clone())
            .datalog(DatalogKind::Rule)?;
        self.0 = Some(next);
        Ok(())
    }

    pub fn add_check(&mut self, check: &Check) -> PhpResult<()> {
        let next = take_builder(&mut self.0)?
            .check(check.0.clone())
            .datalog(DatalogKind::Check)?;
        self.0 = Some(next);
        Ok(())
    }

    pub fn add_code(
        &mut self,
        source: &str,
        params: Option<HashMap<String, MixedValue>>,
        scope_params: Option<HashMap<String, &PublicKey>>,
    ) -> PhpResult<()> {
        let term_params = collect_term_params(params)?;
        let scope = collect_scope_params(scope_params);

        let next = take_builder(&mut self.0)?
            .code_with_params(source, term_params, scope)
            .datalog(DatalogKind::Term)?;
        self.0 = Some(next);
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

fn collect_term_params(
    params: Option<HashMap<String, MixedValue>>,
) -> PhpResult<HashMap<String, biscuit_auth::builder::Term>> {
    match params {
        Some(p) => p
            .iter()
            .map(|(k, v)| mixed_value_to_term(v).map(|term| (k.clone(), term)))
            .collect(),
        None => Ok(HashMap::new()),
    }
}

fn collect_scope_params(
    scope_params: Option<HashMap<String, &PublicKey>>,
) -> HashMap<String, biscuit_auth::PublicKey> {
    match scope_params {
        Some(sp) => sp.iter().map(|(k, v)| (k.clone(), v.0)).collect(),
        None => HashMap::new(),
    }
}
