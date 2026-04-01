#![cfg_attr(windows, feature(abi_vectorcall))]

mod authorizer;
mod biscuit;
mod builders;
mod datalog;
mod errors;
mod helpers;
mod keys;
mod third_party;

pub use authorizer::*;
pub use biscuit::*;
pub use builders::*;
pub use datalog::*;
pub use errors::*;
pub use helpers::*;
pub use keys::*;
pub use third_party::*;

use ext_php_rs::zend::ModuleEntry;
use ext_php_rs::{info_table_end, info_table_row, info_table_start, prelude::*};

pub fn startup(_ty: i32, _mod_num: i32) -> i32 {
    0
}

pub extern "C" fn php_module_info(_module: *mut ModuleEntry) {
    info_table_start!();
    info_table_row!("ext-biscuit-php", "enabled");
    info_table_row!("version", env!("CARGO_PKG_VERSION"));
    info_table_row!("biscuit-auth version", "6.0.0");
    info_table_end!();
}

#[php_module]
#[php(startup = "startup")]
pub fn get_module(module: ModuleBuilder) -> ModuleBuilder {
    module
        .info_function(php_module_info)
        .enumeration::<Algorithm>()
        .class::<Biscuit>()
        .class::<UnverifiedBiscuit>()
        .class::<Authorizer>()
        .class::<AuthorizerBuilder>()
        .class::<BiscuitBuilder>()
        .class::<BlockBuilder>()
        .class::<ThirdPartyRequest>()
        .class::<ThirdPartyBlock>()
        .class::<Rule>()
        .class::<Fact>()
        .class::<Check>()
        .class::<Policy>()
        .class::<KeyPair>()
        .class::<PublicKey>()
        .class::<PrivateKey>()
        .class::<InvalidPrivateKey>()
        .class::<InvalidPublicKey>()
        .class::<InvalidCheck>()
        .class::<InvalidPolicy>()
        .class::<InvalidFact>()
        .class::<InvalidRule>()
        .class::<InvalidTerm>()
        .class::<ThirdPartyRequestError>()
        .class::<AuthorizerError>()
        .class::<BuilderConsumed>()
}
