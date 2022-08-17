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

    pub fn set(&mut self, name: &str, value: MixedValue) -> PhpResult<()> {
        let term_value = match value {
            MixedValue::Long(v) => biscuit_auth::builder::Term::Integer(v as i64),
            MixedValue::Bool(b) => biscuit_auth::builder::Term::Bool(b),
            MixedValue::ParsedStr(s) => biscuit_auth::builder::Term::Str(s),
            MixedValue::None => {
                return Err(PhpException::new(
                    "unexpected value".to_string(),
                    0,
                    unsafe { INVALID_POLICY.expect("did not set exception ce") },
                ))
            }
        };

        self.0.set(name, term_value).map_err(|e| {
            PhpException::new(e.to_string(), 0, unsafe {
                INVALID_POLICY.expect("did not set exception ce")
            })
        })
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
        Ok(Self(biscuit_auth::KeyPair::from(pk.0)))
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

/// This is statics classes entries for storing the right excpetion
static mut INVALID_PRIVATE_KEY: Option<&'static ClassEntry> = None;
static mut INVALID_PUBLIC_KEY: Option<&'static ClassEntry> = None;
static mut INVALID_POLICY: Option<&'static ClassEntry> = None;

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
