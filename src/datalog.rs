use std::collections::HashMap;

use ext_php_rs::prelude::*;

use crate::errors::{InvalidCheck, InvalidFact, InvalidPolicy, InvalidRule, InvalidTerm};
use crate::helpers::{MixedValue, mixed_value_to_term};
use crate::keys::PublicKey;

#[php_class]
#[php(name = "Biscuit\\Auth\\Rule")]
#[derive(Debug, Clone)]
pub struct Rule(pub(crate) biscuit_auth::builder::Rule);

#[php_impl]
impl Rule {
    pub fn __construct(
        source: &str,
        params: Option<HashMap<String, MixedValue>>,
        scope_params: Option<HashMap<String, &PublicKey>>,
    ) -> PhpResult<Self> {
        let mut rule: biscuit_auth::builder::Rule = source
            .try_into()
            .map_err(|e| PhpException::from_class::<InvalidRule>(format!("{}", e)))?;

        if let Some(p) = params {
            p.iter().try_for_each(|(key, value)| {
                rule.set(key, mixed_value_to_term(value)?)
                    .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
            })?;
        }

        if let Some(sp) = scope_params {
            sp.iter().try_for_each(|(key, pk)| {
                rule.set_scope(key, pk.0)
                    .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
            })?;
        }

        Ok(Self(rule))
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
pub struct Fact(pub(crate) biscuit_auth::builder::Fact);

#[php_impl]
impl Fact {
    pub fn __construct(
        source: &str,
        params: Option<HashMap<String, MixedValue>>,
    ) -> PhpResult<Self> {
        let mut fact: biscuit_auth::builder::Fact = source
            .try_into()
            .map_err(|e| PhpException::from_class::<InvalidFact>(format!("{}", e)))?;

        if let Some(p) = params {
            p.iter().try_for_each(|(key, value)| {
                fact.set(key, mixed_value_to_term(value)?)
                    .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
            })?;
        }

        Ok(Self(fact))
    }

    /// @param int|string|bool|null $value
    pub fn set(&mut self, name: &str, value: MixedValue) -> PhpResult<()> {
        let term_value = mixed_value_to_term(&value)?;

        self.0
            .set(name, term_value)
            .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
    }

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
pub struct Check(pub(crate) biscuit_auth::builder::Check);

#[php_impl]
impl Check {
    pub fn __construct(
        source: &str,
        params: Option<HashMap<String, MixedValue>>,
        scope_params: Option<HashMap<String, &PublicKey>>,
    ) -> PhpResult<Self> {
        let mut check: biscuit_auth::builder::Check = source
            .try_into()
            .map_err(|e| PhpException::from_class::<InvalidCheck>(format!("{}", e)))?;

        if let Some(p) = params {
            p.iter().try_for_each(|(key, value)| {
                check
                    .set(key, mixed_value_to_term(value)?)
                    .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
            })?;
        }

        if let Some(sp) = scope_params {
            sp.iter().try_for_each(|(key, pk)| {
                check
                    .set_scope(key, pk.0)
                    .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
            })?;
        }

        Ok(Self(check))
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
pub struct Policy(pub(crate) biscuit_auth::builder::Policy);

#[php_impl]
impl Policy {
    pub fn __construct(
        source: &str,
        params: Option<HashMap<String, MixedValue>>,
        scope_params: Option<HashMap<String, &PublicKey>>,
    ) -> PhpResult<Self> {
        let mut policy: biscuit_auth::builder::Policy = source
            .try_into()
            .map_err(|e| PhpException::from_class::<InvalidPolicy>(format!("{}", e)))?;

        if let Some(p) = params {
            p.iter().try_for_each(|(key, value)| {
                policy
                    .set(key, mixed_value_to_term(value)?)
                    .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
            })?;
        }

        if let Some(sp) = scope_params {
            sp.iter().try_for_each(|(key, pk)| {
                policy
                    .set_scope(key, pk.0)
                    .map_err(|e| PhpException::from_class::<InvalidTerm>(e.to_string()))
            })?;
        }

        Ok(Self(policy))
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
