use biscuit_auth::KeyPair as BiscuitKeyPair;
use ext_php_rs::zend::ModuleEntry;
use ext_php_rs::{info_table_end, info_table_row, info_table_start, prelude::*};

#[php_class(name = "Biscuit\\Auth\\KeyPair")]
#[derive(Debug)]
pub struct KeyPair {
    key_pair: BiscuitKeyPair,
}

#[php_impl]
impl KeyPair {
    pub fn __construct() -> Self {
        Self {
            key_pair: BiscuitKeyPair::new(),
        }
    }

    pub fn get_public_key(&self) -> String {
        self.key_pair.public().print()
    }
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
