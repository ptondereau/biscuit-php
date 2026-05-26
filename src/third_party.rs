use biscuit_auth::ThirdPartyBlock as BiscuitThirdPartyBlock;
use ext_php_rs::prelude::*;

use crate::builders::BlockBuilder;
use crate::errors::{BiscuitError, ResultExt};
use crate::helpers::get_builder;
use crate::keys::PrivateKey;

#[php_class]
#[php(name = "Biscuit\\Auth\\ThirdPartyRequest")]
pub struct ThirdPartyRequest(pub(crate) Option<biscuit_auth::ThirdPartyRequest>);

#[php_impl]
impl ThirdPartyRequest {
    pub fn create_block(
        &mut self,
        private_key: &PrivateKey,
        block: &BlockBuilder,
    ) -> PhpResult<ThirdPartyBlock> {
        let request = self.0.take().ok_or_else(|| {
            PhpException::from(BiscuitError::BuilderConsumed(
                "third-party request has already been consumed",
            ))
        })?;

        let signed = request
            .create_block(&private_key.0, get_builder(&block.0)?.clone())
            .third_party()?;
        Ok(ThirdPartyBlock(signed))
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\ThirdPartyBlock")]
#[derive(Clone)]
pub struct ThirdPartyBlock(pub(crate) BiscuitThirdPartyBlock);
