use ext_php_rs::binary_slice::BinarySlice;
use ext_php_rs::builders::ClassBuilder;
use ext_php_rs::zend::{ce, ClassEntry, ModuleEntry};
use ext_php_rs::{info_table_end, info_table_row, info_table_start, prelude::*};

static mut INVALID_PRIVATE_KEY: Option<&'static ClassEntry> = None;

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

    pub fn public(&self) -> String {
        self.0.public().print()
    }
}

#[php_class(name = "Biscuit\\Auth\\PublicKey")]
#[derive(Debug)]
pub struct PublicKey(biscuit_auth::PublicKey);

#[php_impl]
impl PublicKey {}

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

#[php_startup]
pub fn startup() {
    let ce_invalid_private_key = ClassBuilder::new("Biscuit\\Exception\\InvalidPrivateKey")
        .extends(ce::exception())
        .build()
        .expect("Invalid private key");
    unsafe { INVALID_PRIVATE_KEY.replace(ce_invalid_private_key) };
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
