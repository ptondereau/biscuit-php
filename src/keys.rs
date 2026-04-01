use std::str::FromStr;

use biscuit_auth::KeyPair as BiscuitKeyPair;
use biscuit_auth::builder::Algorithm as BiscuitAlgorithm;
use ext_php_rs::binary_slice::BinarySlice;
use ext_php_rs::prelude::*;

use crate::errors::{InvalidPrivateKey, InvalidPublicKey};

#[php_enum]
#[php(name = "Biscuit\\Auth\\Algorithm")]
pub enum Algorithm {
    #[php(value = 0)]
    Ed25519,
    #[php(name = "Secp256r1", value = 1)]
    Secp256r1,
}

impl From<Algorithm> for BiscuitAlgorithm {
    fn from(alg: Algorithm) -> Self {
        match alg {
            Algorithm::Ed25519 => BiscuitAlgorithm::Ed25519,
            Algorithm::Secp256r1 => BiscuitAlgorithm::Secp256r1,
        }
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\KeyPair")]
#[derive(Debug)]
pub struct KeyPair(BiscuitKeyPair);

#[php_impl]
impl KeyPair {
    pub fn __construct(alg: Option<Algorithm>) -> Self {
        let algorithm = alg.unwrap_or(Algorithm::Ed25519).into();
        Self(BiscuitKeyPair::new_with_algorithm(algorithm))
    }

    #[php(name = "fromPrivateKey")]
    pub fn from_private_key(private_key: &PrivateKey) -> Self {
        Self(BiscuitKeyPair::from(&private_key.0))
    }

    #[php(name = "getPublicKey")]
    pub fn get_public_key(&self) -> PublicKey {
        PublicKey(self.0.public())
    }

    #[php(name = "getPrivateKey")]
    pub fn get_private_key(&self) -> PrivateKey {
        PrivateKey(self.0.private())
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\PublicKey")]
#[derive(Debug, Clone, Copy)]
pub struct PublicKey(pub(crate) biscuit_auth::PublicKey);

#[php_impl]
impl PublicKey {
    pub fn __construct(data: &str) -> PhpResult<Self> {
        biscuit_auth::PublicKey::from_str(data)
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPublicKey>(e.to_string()))
    }

    #[php(name = "fromBytes")]
    pub fn from_bytes(data: BinarySlice<u8>, alg: Option<Algorithm>) -> PhpResult<Self> {
        let algorithm = alg.unwrap_or(Algorithm::Ed25519).into();
        biscuit_auth::PublicKey::from_bytes(data.as_ref(), algorithm)
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPublicKey>(e.to_string()))
    }

    #[php(name = "fromPem")]
    pub fn from_pem(pem: &str) -> PhpResult<Self> {
        biscuit_auth::PublicKey::from_pem(pem)
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPublicKey>(e.to_string()))
    }

    #[php(name = "fromDer")]
    pub fn from_der(der: BinarySlice<u8>) -> PhpResult<Self> {
        biscuit_auth::PublicKey::from_der(der.as_ref())
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPublicKey>(e.to_string()))
    }

    pub fn to_bytes(&self) -> Vec<u8> {
        self.0.to_bytes()
    }

    pub fn to_hex(&self) -> String {
        self.0.to_string()
    }

    pub fn __to_string(&self) -> String {
        self.0.to_string()
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\PrivateKey")]
#[derive(Debug, Clone)]
pub struct PrivateKey(pub(crate) biscuit_auth::PrivateKey);

#[php_impl]
impl PrivateKey {
    pub fn __construct(data: &str) -> PhpResult<Self> {
        biscuit_auth::PrivateKey::from_str(data)
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPrivateKey>(e.to_string()))
    }

    #[php(name = "fromBytes")]
    pub fn from_bytes(data: BinarySlice<u8>, alg: Option<Algorithm>) -> PhpResult<Self> {
        let algorithm = alg.unwrap_or(Algorithm::Ed25519).into();
        biscuit_auth::PrivateKey::from_bytes(data.as_ref(), algorithm)
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPrivateKey>(e.to_string()))
    }

    #[php(name = "fromPem")]
    pub fn from_pem(pem: &str) -> PhpResult<Self> {
        biscuit_auth::PrivateKey::from_pem(pem)
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPrivateKey>(e.to_string()))
    }

    #[php(name = "fromDer")]
    pub fn from_der(der: BinarySlice<u8>) -> PhpResult<Self> {
        biscuit_auth::PrivateKey::from_der(der.as_ref())
            .map(Self)
            .map_err(|e| PhpException::from_class::<InvalidPrivateKey>(e.to_string()))
    }

    pub fn generate(alg: Option<Algorithm>) -> Self {
        let algorithm = alg.unwrap_or(Algorithm::Ed25519).into();
        let keypair = BiscuitKeyPair::new_with_algorithm(algorithm);
        Self(keypair.private())
    }

    #[php(name = "getPublicKey")]
    pub fn get_public_key(&self) -> PublicKey {
        let keypair = BiscuitKeyPair::from(&self.0);
        PublicKey(keypair.public())
    }

    pub fn to_bytes(&self) -> Vec<u8> {
        self.0.to_bytes().to_vec()
    }

    pub fn to_hex(&self) -> String {
        self.0.to_prefixed_string()
    }

    pub fn __to_string(&self) -> String {
        self.0.to_prefixed_string()
    }
}
