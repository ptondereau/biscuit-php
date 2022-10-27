use std::collections::HashMap;

use ext_php_rs::binary_slice::BinarySlice;
use ext_php_rs::builders::ClassBuilder;
use ext_php_rs::zend::{ce, ClassEntry, ModuleEntry};
use ext_php_rs::{info_table_end, info_table_row, info_table_start, prelude::*};

#[derive(Debug, ZvalConvert)]
pub enum MixedValue {
    Long(u64),
    Bool(bool),
    ParsedStr(String),
    None,
}

#[php_class(name = "Biscuit\\Auth\\BlockBuilder")]
#[derive(Debug)]
pub struct BlockBuilder(biscuit_auth::builder::BlockBuilder);

#[php_impl]
impl BlockBuilder {
    pub fn __construct() -> Self {
        Self(biscuit_auth::builder::BlockBuilder::default())
    }

    pub fn add_fact(&mut self, fact: &Fact) -> PhpResult<()> {
        self.0.add_fact(fact.0.clone()).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_FACT.expect("did not set exception ce")
            })
        })
    }

    pub fn add_rule(&mut self, rule: &Rule) -> PhpResult<()> {
        self.0.add_rule(rule.0.clone()).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_RULE.expect("did not set exception ce")
            })
        })
    }

    pub fn add_check(&mut self, check: &Check) -> PhpResult<()> {
        self.0.add_check(check.0.clone()).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_CHECK.expect("did not set exception ce")
            })
        })
    }

    pub fn add_code(&mut self, source: &str) -> PhpResult<()> {
        self.0.add_code(source).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_TERM.expect("did not set exception ce")
            })
        })
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

        self.0
            .add_code_with_params(source, term_params, scope_params_cloned)
            .map_err(|e| {
                PhpException::new(e.to_string(), 0, unsafe {
                    INVALID_TERM.expect("did not set exception ce")
                })
            })
    }

    pub fn __to_string(&mut self) -> String {
        format!("{}", self)
    }
}

impl std::fmt::Display for BlockBuilder {
    fn fmt(&self, f: &mut std::fmt::Formatter) -> std::fmt::Result {
        write!(f, "{}", self.0)
    }
}

#[php_class(name = "Biscuit\\Auth\\Rule")]
#[derive(Debug)]
pub struct Rule(biscuit_auth::builder::Rule);

#[php_impl]
impl Rule {
    pub fn __construct(source: &str) -> PhpResult<Self> {
        source.try_into().map(Self).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_RULE.expect("did not set exception ce")
            })
        })
    }

    /// @param int|string|bool|null $value
    pub fn set(&mut self, name: &str, value: MixedValue) -> PhpResult<()> {
        let term_value = mixed_value_to_term(&value)?;

        self.0.set(name, term_value).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_TERM.expect("did not set exception ce")
            })
        })
    }

    pub fn __to_string(&mut self) -> String {
        format!("{}", self)
    }
}

impl std::fmt::Display for Rule {
    fn fmt(&self, f: &mut std::fmt::Formatter) -> std::fmt::Result {
        write!(f, "{}", self.0)
    }
}

#[php_class(name = "Biscuit\\Auth\\Fact")]
#[derive(Debug)]
pub struct Fact(biscuit_auth::builder::Fact);

#[php_impl]
impl Fact {
    pub fn __construct(source: &str) -> PhpResult<Self> {
        source.try_into().map(Self).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_FACT.expect("did not set exception ce")
            })
        })
    }

    /// @param int|string|bool|null $value
    pub fn set(&mut self, name: &str, value: MixedValue) -> PhpResult<()> {
        let term_value = mixed_value_to_term(&value)?;

        self.0.set(name, term_value).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_TERM.expect("did not set exception ce")
            })
        })
    }

    pub fn __to_string(&mut self) -> String {
        format!("{}", self)
    }
}

impl std::fmt::Display for Fact {
    fn fmt(&self, f: &mut std::fmt::Formatter) -> std::fmt::Result {
        write!(f, "{}", self.0)
    }
}

#[php_class(name = "Biscuit\\Auth\\Check")]
#[derive(Debug)]
pub struct Check(biscuit_auth::builder::Check);

#[php_impl]
impl Check {
    pub fn __construct(source: &str) -> PhpResult<Self> {
        source.try_into().map(Self).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_CHECK.expect("did not set exception ce")
            })
        })
    }

    /// @param int|string|bool|null $value
    pub fn set(&mut self, name: &str, value: MixedValue) -> PhpResult<()> {
        let term_value = mixed_value_to_term(&value)?;

        self.0.set(name, term_value).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_TERM.expect("did not set exception ce")
            })
        })
    }

    pub fn __to_string(&mut self) -> String {
        format!("{}", self)
    }
}

impl std::fmt::Display for Check {
    fn fmt(&self, f: &mut std::fmt::Formatter) -> std::fmt::Result {
        write!(f, "{}", self.0)
    }
}

#[php_class(name = "Biscuit\\Auth\\Policy")]
#[derive(Debug)]
pub struct Policy(biscuit_auth::builder::Policy);

#[php_impl]
impl Policy {
    pub fn __construct(source: &str) -> PhpResult<Self> {
        source.try_into().map(Self).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_POLICY.expect("did not set exception ce")
            })
        })
    }

    /// @param int|string|bool|null $value
    pub fn set(&mut self, name: &str, value: MixedValue) -> PhpResult<()> {
        let term_value = mixed_value_to_term(&value)?;

        self.0.set(name, term_value).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_TERM.expect("did not set exception ce")
            })
        })
    }

    pub fn __to_string(&mut self) -> String {
        format!("{}", self)
    }
}

impl std::fmt::Display for Policy {
    fn fmt(&self, f: &mut std::fmt::Formatter) -> std::fmt::Result {
        write!(f, "{}", self.0)
    }
}

#[php_class(name = "Biscuit\\Auth\\KeyPair")]
#[derive(Debug)]
pub struct KeyPair(biscuit_auth::KeyPair);

#[php_impl]
impl KeyPair {
    pub fn __construct() -> Self {
        Self(biscuit_auth::KeyPair::new())
    }

    pub fn from_private_key(private_key: BinarySlice<u8>) -> PhpResult<Self> {
        let pk = PrivateKey::__construct(private_key)?;
        Ok(Self(biscuit_auth::KeyPair::from(&pk.0)))
    }

    pub fn public(&self) -> PublicKey {
        PublicKey(self.0.public())
    }

    pub fn private(&self) -> PrivateKey {
        PrivateKey(self.0.private())
    }
}

#[php_class(name = "Biscuit\\Auth\\PublicKey")]
#[derive(Debug)]
pub struct PublicKey(biscuit_auth::PublicKey);

#[php_impl]
impl PublicKey {
    pub fn __construct(key: BinarySlice<u8>) -> PhpResult<Self> {
        let key = biscuit_auth::PublicKey::from_bytes(*key).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_PUBLIC_KEY.expect("did not set exception ce")
            })
        })?;

        Ok(Self(key))
    }

    pub fn to_hex(&self) -> String {
        hex::encode(&self.0.to_bytes())
    }
}

#[php_class(name = "Biscuit\\Auth\\PrivateKey")]
#[derive(Debug)]
pub struct PrivateKey(biscuit_auth::PrivateKey);

#[php_impl]
impl PrivateKey {
    pub fn __construct(key: BinarySlice<u8>) -> PhpResult<Self> {
        let key = biscuit_auth::PrivateKey::from_bytes(*key).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_PRIVATE_KEY.expect("did not set exception ce")
            })
        })?;

        Ok(Self(key))
    }

    pub fn to_hex(&self) -> String {
        hex::encode(&self.0.to_bytes())
    }
}

fn mixed_value_to_term(value: &MixedValue) -> PhpResult<biscuit_auth::builder::Term> {
    match value {
        MixedValue::Long(v) => Ok(biscuit_auth::builder::Term::Integer(*v as i64)),
        MixedValue::Bool(b) => Ok(biscuit_auth::builder::Term::Bool(*b)),
        MixedValue::ParsedStr(s) => Ok(biscuit_auth::builder::Term::Str(s.clone())),
        MixedValue::None => {
            return Err(PhpException::new(
                "unexpected value".to_string(),
                0,
                unsafe { INVALID_TERM.expect("did not set exception ce") },
            ))
        }
    }
}

/// This is statics classes entries for storing the right excpetion
static mut INVALID_PRIVATE_KEY: Option<&'static ClassEntry> = None;
static mut INVALID_PUBLIC_KEY: Option<&'static ClassEntry> = None;
static mut INVALID_CHECK: Option<&'static ClassEntry> = None;
static mut INVALID_POLICY: Option<&'static ClassEntry> = None;
static mut INVALID_FACT: Option<&'static ClassEntry> = None;
static mut INVALID_RULE: Option<&'static ClassEntry> = None;
static mut INVALID_TERM: Option<&'static ClassEntry> = None;

#[php_startup]
pub fn startup() {
    let ce_invalid_private_key = ClassBuilder::new("Biscuit\\Exception\\InvalidPrivateKey")
        .extends(ce::exception())
        .build()
        .expect("Invalid private key");
    unsafe { INVALID_PRIVATE_KEY.replace(ce_invalid_private_key) };

    let ce_invalid_public_key = ClassBuilder::new("Biscuit\\Exception\\InvalidPublicKey")
        .extends(ce::exception())
        .build()
        .expect("Invalid public key");
    unsafe { INVALID_PUBLIC_KEY.replace(ce_invalid_public_key) };

    let ce_invalid_policy = ClassBuilder::new("Biscuit\\Exception\\InvalidPolicy")
        .extends(ce::exception())
        .build()
        .expect("Invalid policy");
    unsafe { INVALID_POLICY.replace(ce_invalid_policy) };

    let ce_invalid_check = ClassBuilder::new("Biscuit\\Exception\\InvalidCheck")
        .extends(ce::exception())
        .build()
        .expect("Invalid check");
    unsafe { INVALID_CHECK.replace(ce_invalid_check) };

    let ce_invalid_fact = ClassBuilder::new("Biscuit\\Exception\\InvalidFact")
        .extends(ce::exception())
        .build()
        .expect("Invalid fact");
    unsafe { INVALID_FACT.replace(ce_invalid_fact) };

    let ce_invalid_rule = ClassBuilder::new("Biscuit\\Exception\\InvalidRule")
        .extends(ce::exception())
        .build()
        .expect("Invalid rule");
    unsafe { INVALID_RULE.replace(ce_invalid_rule) };

    let ce_invalid_term = ClassBuilder::new("Biscuit\\Exception\\InvalidTerm")
        .extends(ce::exception())
        .build()
        .expect("Invalid term");
    unsafe { INVALID_TERM.replace(ce_invalid_term) };
}

/// Used by the `phpinfo()` function and when you run `php -i`.
pub extern "C" fn php_module_info(_module: *mut ModuleEntry) {
    info_table_start!();
    info_table_row!("ext-biscuit-php", "enabled");
    info_table_end!();
}

// Required to register the extension with PHP.
#[php_module]
pub fn phpmodule(module: ModuleBuilder) -> ModuleBuilder {
    module.info_function(php_module_info)
}
