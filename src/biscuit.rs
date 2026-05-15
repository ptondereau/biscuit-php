use ext_php_rs::binary_slice::BinarySlice;
use ext_php_rs::prelude::*;

use crate::builders::{BiscuitBuilder, BlockBuilder};
use crate::errors::{BuildKind, FormatKind, ResultExt};
use crate::helpers::get_builder;
use crate::keys::PublicKey;
use crate::third_party::{ThirdPartyBlock, ThirdPartyRequest};

#[php_class]
#[php(name = "Biscuit\\Auth\\Biscuit")]
#[derive(Clone)]
pub struct Biscuit(pub(crate) biscuit_auth::Biscuit);

impl Biscuit {
    pub fn wrap(inner: biscuit_auth::Biscuit) -> Self {
        Self(inner)
    }
}

#[php_impl]
impl Biscuit {
    #[php(name = "builder")]
    pub fn builder() -> BiscuitBuilder {
        BiscuitBuilder::new()
    }

    #[php(name = "fromBytes")]
    pub fn from_bytes(data: BinarySlice<u8>, root: &PublicKey) -> PhpResult<Self> {
        Ok(Self(
            biscuit_auth::Biscuit::from(data.as_ref(), root.0).format(FormatKind::Bytes)?,
        ))
    }

    #[php(name = "fromBase64")]
    pub fn from_base64(data: &str, root: &PublicKey) -> PhpResult<Self> {
        Ok(Self(
            biscuit_auth::Biscuit::from_base64(data, root.0).format(FormatKind::Base64)?,
        ))
    }

    pub fn to_bytes(&self) -> PhpResult<Vec<u8>> {
        Ok(self.0.to_vec().format(FormatKind::Bytes)?)
    }

    pub fn to_base64(&self) -> PhpResult<String> {
        Ok(self.0.to_base64().format(FormatKind::Base64)?)
    }

    pub fn block_count(&self) -> usize {
        self.0.block_count()
    }

    pub fn block_source(&self, index: i64) -> PhpResult<String> {
        Ok(self
            .0
            .print_block_source(index as usize)
            .format(FormatKind::Snapshot)?)
    }

    pub fn append(&self, block: &BlockBuilder) -> PhpResult<Self> {
        Ok(Self(
            self.0
                .append(get_builder(&block.0)?.clone())
                .build(BuildKind::Append)?,
        ))
    }

    pub fn append_third_party(
        &self,
        external_key: &PublicKey,
        block: &ThirdPartyBlock,
    ) -> PhpResult<Self> {
        Ok(Self(
            self.0
                .append_third_party(external_key.0, block.0.clone())
                .build(BuildKind::ThirdPartyAppend)?,
        ))
    }

    pub fn third_party_request(&self) -> PhpResult<ThirdPartyRequest> {
        let request = self.0.third_party_request().third_party()?;
        Ok(ThirdPartyRequest(Some(request)))
    }

    pub fn revocation_ids(&self) -> Vec<String> {
        self.0
            .revocation_identifiers()
            .into_iter()
            .map(hex::encode)
            .collect()
    }

    pub fn block_external_key(&self, index: i64) -> PhpResult<Option<PublicKey>> {
        let key = self
            .0
            .block_external_key(index as usize)
            .format(FormatKind::Snapshot)?;
        Ok(key.map(PublicKey))
    }

    pub fn __to_string(&self) -> String {
        self.0.print()
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\UnverifiedBiscuit")]
#[derive(Clone)]
pub struct UnverifiedBiscuit(biscuit_auth::UnverifiedBiscuit);

#[php_impl]
impl UnverifiedBiscuit {
    #[php(name = "fromBase64")]
    pub fn from_base64(data: &str) -> PhpResult<Self> {
        Ok(Self(
            biscuit_auth::UnverifiedBiscuit::from_base64(data).format(FormatKind::Base64)?,
        ))
    }

    pub fn root_key_id(&self) -> Option<u32> {
        self.0.root_key_id()
    }

    pub fn block_count(&self) -> usize {
        self.0.block_count()
    }

    pub fn block_source(&self, index: i64) -> PhpResult<String> {
        Ok(self
            .0
            .print_block_source(index as usize)
            .format(FormatKind::Snapshot)?)
    }

    pub fn append(&self, block: &BlockBuilder) -> PhpResult<Self> {
        Ok(Self(
            self.0
                .append(get_builder(&block.0)?.clone())
                .build(BuildKind::Append)?,
        ))
    }

    pub fn revocation_ids(&self) -> Vec<String> {
        self.0
            .revocation_identifiers()
            .into_iter()
            .map(hex::encode)
            .collect()
    }

    pub fn verify(&self, root: &PublicKey) -> PhpResult<Biscuit> {
        Ok(Biscuit(
            self.0
                .clone()
                .verify(root.0)
                .format(FormatKind::Signature)?,
        ))
    }
}
