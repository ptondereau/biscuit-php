use std::collections::HashMap;

use biscuit_parser::error::ParseError as UpstreamParseError;
use ext_php_rs::prelude::*;

use crate::errors::{DatalogKind, ResultExt};
use crate::helpers::{MixedValue, mixed_value_to_term};
use crate::keys::PublicKey;

#[php_class]
#[php(name = "Biscuit\\Auth\\ParseError")]
#[derive(Debug, Clone)]
pub struct ParseError {
    input: String,
    message: Option<String>,
}

impl ParseError {
    pub(crate) fn from_upstream(p: &UpstreamParseError) -> Self {
        Self {
            input: p.input.clone(),
            message: p.message.clone(),
        }
    }
}

#[php_impl]
impl ParseError {
    pub fn get_input(&self) -> String {
        self.input.clone()
    }

    pub fn get_message(&self) -> Option<String> {
        self.message.clone()
    }
}

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
        let mut rule: biscuit_auth::builder::Rule =
            biscuit_auth::builder::Rule::try_from(source).datalog(DatalogKind::Rule)?;

        if let Some(p) = params {
            for (key, value) in &p {
                let term = mixed_value_to_term(value)?;
                rule.set(key, term).datalog(DatalogKind::Term)?;
            }
        }

        if let Some(sp) = scope_params {
            for (key, pk) in &sp {
                rule.set_scope(key, pk.0).datalog(DatalogKind::Scope)?;
            }
        }

        Ok(Self(rule))
    }

    /// @param int|string|bool|null $value
    pub fn set(&mut self, name: &str, value: MixedValue) -> PhpResult<()> {
        let term_value = mixed_value_to_term(&value)?;
        self.0.set(name, term_value).datalog(DatalogKind::Term)?;
        Ok(())
    }

    pub fn set_scope(&mut self, name: &str, key: &PublicKey) -> PhpResult<()> {
        self.0.set_scope(name, key.0).datalog(DatalogKind::Scope)?;
        Ok(())
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
        let mut fact: biscuit_auth::builder::Fact =
            biscuit_auth::builder::Fact::try_from(source).datalog(DatalogKind::Fact)?;

        if let Some(p) = params {
            for (key, value) in &p {
                let term = mixed_value_to_term(value)?;
                fact.set(key, term).datalog(DatalogKind::Term)?;
            }
        }

        Ok(Self(fact))
    }

    /// @param int|string|bool|null $value
    pub fn set(&mut self, name: &str, value: MixedValue) -> PhpResult<()> {
        let term_value = mixed_value_to_term(&value)?;
        self.0.set(name, term_value).datalog(DatalogKind::Term)?;
        Ok(())
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
        let mut check: biscuit_auth::builder::Check =
            biscuit_auth::builder::Check::try_from(source).datalog(DatalogKind::Check)?;

        if let Some(p) = params {
            for (key, value) in &p {
                let term = mixed_value_to_term(value)?;
                check.set(key, term).datalog(DatalogKind::Term)?;
            }
        }

        if let Some(sp) = scope_params {
            for (key, pk) in &sp {
                check.set_scope(key, pk.0).datalog(DatalogKind::Scope)?;
            }
        }

        Ok(Self(check))
    }

    /// @param int|string|bool|null $value
    pub fn set(&mut self, name: &str, value: MixedValue) -> PhpResult<()> {
        let term_value = mixed_value_to_term(&value)?;
        self.0.set(name, term_value).datalog(DatalogKind::Term)?;
        Ok(())
    }

    pub fn set_scope(&mut self, name: &str, key: &PublicKey) -> PhpResult<()> {
        self.0.set_scope(name, key.0).datalog(DatalogKind::Scope)?;
        Ok(())
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
        let mut policy: biscuit_auth::builder::Policy =
            biscuit_auth::builder::Policy::try_from(source).datalog(DatalogKind::Policy)?;

        if let Some(p) = params {
            for (key, value) in &p {
                let term = mixed_value_to_term(value)?;
                policy.set(key, term).datalog(DatalogKind::Term)?;
            }
        }

        if let Some(sp) = scope_params {
            for (key, pk) in &sp {
                policy.set_scope(key, pk.0).datalog(DatalogKind::Scope)?;
            }
        }

        Ok(Self(policy))
    }

    /// @param int|string|bool|null $value
    pub fn set(&mut self, name: &str, value: MixedValue) -> PhpResult<()> {
        let term_value = mixed_value_to_term(&value)?;
        self.0.set(name, term_value).datalog(DatalogKind::Term)?;
        Ok(())
    }

    pub fn set_scope(&mut self, name: &str, key: &PublicKey) -> PhpResult<()> {
        self.0.set_scope(name, key.0).datalog(DatalogKind::Scope)?;
        Ok(())
    }

    pub fn __to_string(&self) -> String {
        format!("{}", self.0)
    }
}
