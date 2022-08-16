use ext_php_rs::zend::ModuleEntry;
use ext_php_rs::{info_table_end, info_table_row, info_table_start, prelude::*};

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
