use std::collections::HashMap;

use biscuit_auth::RootKeyProvider;
use biscuit_auth::error::Format;
use ext_php_rs::binary_slice::BinarySlice;
use ext_php_rs::convert::FromZval;
use ext_php_rs::exception::PhpException;
use ext_php_rs::prelude::*;
use ext_php_rs::types::Zval;
use ext_php_rs::zend::ce;

use crate::builders::{BiscuitBuilder, BlockBuilder};
use crate::errors::{BuildKind, FormatKind, ResultExt};
use crate::helpers::get_builder;
use crate::keys::PublicKey;
use crate::third_party::{ThirdPartyBlock, ThirdPartyRequest};

pub enum RootKeys<'a> {
    Single(&'a PublicKey),
    Set(HashMap<i64, &'a PublicKey>),
}

impl<'a> TryFrom<&'a Zval> for RootKeys<'a> {
    type Error = PhpException;

    fn try_from(zval: &'a Zval) -> Result<Self, Self::Error> {
        <&PublicKey>::from_zval(zval)
            .map(Self::Single)
            .or_else(|| HashMap::from_zval(zval).map(Self::Set))
            .ok_or_else(|| {
                PhpException::new(
                    "root must be a PublicKey or an array<int, PublicKey>".into(),
                    0,
                    ce::type_error(),
                )
            })
    }
}

impl RootKeyProvider for RootKeys<'_> {
    fn choose(&self, key_id: Option<u32>) -> Result<biscuit_auth::PublicKey, Format> {
        match self {
            Self::Single(key) => Ok(key.0),
            Self::Set(keys) => key_id
                .and_then(|id| keys.get(&i64::from(id)))
                .map(|key| key.0)
                .ok_or(Format::UnknownPublicKey),
        }
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\Biscuit")]
#[derive(Clone)]
pub struct Biscuit(pub(crate) biscuit_auth::Biscuit);

#[php_impl]
impl Biscuit {
    pub fn builder() -> BiscuitBuilder {
        BiscuitBuilder(Some(biscuit_auth::builder::BiscuitBuilder::new()))
    }

    pub fn from_bytes(data: BinarySlice<u8>, root: &Zval) -> PhpResult<Self> {
        let root = RootKeys::try_from(root)?;
        Ok(Self(
            biscuit_auth::Biscuit::from(data.as_ref(), root).format(FormatKind::Bytes)?,
        ))
    }

    pub fn from_base64(data: &str, root: &Zval) -> PhpResult<Self> {
        let root = RootKeys::try_from(root)?;
        Ok(Self(
            biscuit_auth::Biscuit::from_base64(data, root).format(FormatKind::Base64)?,
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

    pub fn verify(&self, root: &Zval) -> PhpResult<Biscuit> {
        let root = RootKeys::try_from(root)?;
        Ok(Biscuit(
            self.0.clone().verify(root).format(FormatKind::Signature)?,
        ))
    }
}
