use biscuit_auth::error::{Logic, Token};
use biscuit_parser::error::LanguageError;
use ext_php_rs::class::RegisteredClass;
use ext_php_rs::convert::IntoZval;
use ext_php_rs::ffi::{zend_class_entry, zend_object};
use ext_php_rs::prelude::*;
use ext_php_rs::types::{ZendClassObject, Zval};
use ext_php_rs::zend::ce;
use std::os::raw::c_char;
use thiserror::Error;

use crate::authorization::{AuthorizationException, FailedCheck, MatchedPolicy};
use crate::datalog::ParseError;

// SAFETY: `zend_update_property_stringl` is a standard ZEND_API function exported
// by libphp; safe to call during PHP request execution.
unsafe extern "C" {
    fn zend_update_property_stringl(
        scope: *mut zend_class_entry,
        object: *mut zend_object,
        name: *const c_char,
        name_length: usize,
        value: *const c_char,
        value_len: usize,
    );
}

fn populate_exception_message(zval: &mut Zval, message: &str) {
    let Some(obj) = zval.object_mut() else {
        return;
    };
    unsafe {
        zend_update_property_stringl(
            std::ptr::from_ref(ce::exception()).cast_mut(),
            std::ptr::from_mut::<ext_php_rs::types::ZendObject>(obj),
            b"message".as_ptr().cast::<c_char>(),
            7,
            message.as_ptr().cast::<c_char>(),
            message.len(),
        );
    }
}

type BoxedError = Box<dyn std::error::Error + Send + Sync + 'static>;

#[derive(Debug, Clone, Copy, PartialEq, Eq)]
#[repr(i32)]
pub(crate) enum KeyKind {
    PublicKey = 1,
    PrivateKey = 2,
}

#[derive(Debug, Clone, Copy, PartialEq, Eq)]
#[repr(i32)]
pub(crate) enum DatalogKind {
    Fact = 1,
    Rule = 2,
    Check = 3,
    Policy = 4,
    Term = 5,
    Scope = 6,
}

#[derive(Debug, Clone, Copy, PartialEq, Eq)]
#[repr(i32)]
pub(crate) enum FormatKind {
    Base64 = 1,
    Bytes = 2,
    Signature = 3,
    Snapshot = 4,
}

#[derive(Debug, Clone, Copy, PartialEq, Eq)]
#[repr(i32)]
pub(crate) enum BuildKind {
    Token = 1,
    Append = 2,
    Authorizer = 3,
    ThirdPartyAppend = 4,
}

#[derive(Debug, Error)]
pub(crate) enum BiscuitError {
    #[error("{source}")]
    Key {
        kind: KeyKind,
        #[source]
        source: BoxedError,
    },
    #[error("{source}")]
    Datalog {
        kind: DatalogKind,
        #[source]
        source: BoxedError,
    },
    #[error("{source}")]
    Format {
        kind: FormatKind,
        #[source]
        source: BoxedError,
    },
    #[error("{source}")]
    Build {
        kind: BuildKind,
        #[source]
        source: BoxedError,
    },
    #[error("authorization failed: {source}")]
    Authorization {
        #[source]
        source: biscuit_auth::error::Token,
        policies: Vec<biscuit_auth::builder::Policy>,
    },
    #[error("{source}")]
    ThirdParty {
        #[source]
        source: BoxedError,
    },
    #[error("{0}")]
    BuilderConsumed(&'static str),
}

#[derive(Debug, Error)]
#[error("{0}")]
pub(crate) struct StaticError(pub(crate) &'static str);

impl BiscuitError {
    pub(crate) fn authorization(
        source: biscuit_auth::error::Token,
        policies: Vec<biscuit_auth::builder::Policy>,
    ) -> Self {
        Self::Authorization { source, policies }
    }
}

pub(crate) trait ResultExt<T> {
    fn key(self, kind: KeyKind) -> Result<T, BiscuitError>;
    fn datalog(self, kind: DatalogKind) -> Result<T, BiscuitError>;
    fn format(self, kind: FormatKind) -> Result<T, BiscuitError>;
    fn build(self, kind: BuildKind) -> Result<T, BiscuitError>;
    fn third_party(self) -> Result<T, BiscuitError>;
}

impl<T, E> ResultExt<T> for Result<T, E>
where
    E: std::error::Error + Send + Sync + 'static,
{
    fn key(self, kind: KeyKind) -> Result<T, BiscuitError> {
        self.map_err(|source| BiscuitError::Key {
            kind,
            source: Box::new(source),
        })
    }

    fn datalog(self, kind: DatalogKind) -> Result<T, BiscuitError> {
        self.map_err(|source| BiscuitError::Datalog {
            kind,
            source: Box::new(source),
        })
    }

    fn format(self, kind: FormatKind) -> Result<T, BiscuitError> {
        self.map_err(|source| BiscuitError::Format {
            kind,
            source: Box::new(source),
        })
    }

    fn build(self, kind: BuildKind) -> Result<T, BiscuitError> {
        self.map_err(|source| BiscuitError::Build {
            kind,
            source: Box::new(source),
        })
    }

    fn third_party(self) -> Result<T, BiscuitError> {
        self.map_err(|source| BiscuitError::ThirdParty {
            source: Box::new(source),
        })
    }
}

pub(crate) fn collect_chain(err: &(dyn std::error::Error + 'static)) -> String {
    let mut parts: Vec<String> = vec![err.to_string()];
    let mut current = err.source();
    while let Some(src) = current {
        let part = src.to_string();
        if parts.last() != Some(&part) {
            parts.push(part);
        }
        current = src.source();
    }
    parts.join(": ")
}

#[php_class]
#[php(name = "Biscuit\\Exception\\BiscuitException")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default, Clone)]
pub struct BiscuitException;

#[php_class]
#[php(name = "Biscuit\\Exception\\KeyException")]
#[php(extends(BiscuitException))]
#[derive(Default, Clone)]
pub struct KeyException;

macro_rules! marker_subclass {
    ($struct_name:ident, $php_name:literal, $parent:ident) => {
        #[php_class]
        #[php(name = $php_name)]
        #[php(extends($parent))]
        #[derive(Default, Clone)]
        pub struct $struct_name;
    };
}

marker_subclass!(
    PublicKeyException,
    "Biscuit\\Exception\\PublicKeyException",
    KeyException
);
marker_subclass!(
    PrivateKeyException,
    "Biscuit\\Exception\\PrivateKeyException",
    KeyException
);

#[php_class]
#[php(name = "Biscuit\\Exception\\DatalogException")]
#[php(extends(BiscuitException))]
#[derive(Debug, Default, Clone)]
pub struct DatalogException;

#[php_impl]
impl DatalogException {
    pub fn get_parse_errors(&self) -> Option<Vec<ParseError>> {
        None
    }

    pub fn get_missing_parameters(&self) -> Option<Vec<String>> {
        None
    }

    pub fn get_unused_parameters(&self) -> Option<Vec<String>> {
        None
    }
}

macro_rules! datalog_subclass {
    ($struct_name:ident, $php_name:literal) => {
        #[php_class]
        #[php(name = $php_name)]
        #[php(extends(DatalogException))]
        #[derive(Debug, Default, Clone)]
        pub struct $struct_name {
            parse_errors: Option<Vec<ParseError>>,
            missing_parameters: Option<Vec<String>>,
            unused_parameters: Option<Vec<String>>,
        }

        impl $struct_name {
            pub(crate) fn new(
                parse_errors: Option<Vec<ParseError>>,
                missing_parameters: Option<Vec<String>>,
                unused_parameters: Option<Vec<String>>,
            ) -> Self {
                Self {
                    parse_errors,
                    missing_parameters,
                    unused_parameters,
                }
            }
        }

        #[php_impl]
        impl $struct_name {
            pub fn get_parse_errors(&self) -> Option<Vec<ParseError>> {
                self.parse_errors.clone()
            }

            pub fn get_missing_parameters(&self) -> Option<Vec<String>> {
                self.missing_parameters.clone()
            }

            pub fn get_unused_parameters(&self) -> Option<Vec<String>> {
                self.unused_parameters.clone()
            }
        }
    };
}

datalog_subclass!(FactException, "Biscuit\\Exception\\FactException");
datalog_subclass!(RuleException, "Biscuit\\Exception\\RuleException");
datalog_subclass!(CheckException, "Biscuit\\Exception\\CheckException");
datalog_subclass!(PolicyException, "Biscuit\\Exception\\PolicyException");
datalog_subclass!(TermException, "Biscuit\\Exception\\TermException");
datalog_subclass!(ScopeException, "Biscuit\\Exception\\ScopeException");

#[php_class]
#[php(name = "Biscuit\\Exception\\FormatException")]
#[php(extends(BiscuitException))]
#[derive(Default, Clone)]
pub struct FormatException;

marker_subclass!(
    Base64Exception,
    "Biscuit\\Exception\\Base64Exception",
    FormatException
);
marker_subclass!(
    BytesException,
    "Biscuit\\Exception\\BytesException",
    FormatException
);
marker_subclass!(
    SignatureException,
    "Biscuit\\Exception\\SignatureException",
    FormatException
);
marker_subclass!(
    SnapshotException,
    "Biscuit\\Exception\\SnapshotException",
    FormatException
);

#[php_class]
#[php(name = "Biscuit\\Exception\\BuildException")]
#[php(extends(BiscuitException))]
#[derive(Default, Clone)]
pub struct BuildException;

marker_subclass!(
    BiscuitBuildException,
    "Biscuit\\Exception\\BiscuitBuildException",
    BuildException
);
marker_subclass!(
    BlockAppendException,
    "Biscuit\\Exception\\BlockAppendException",
    BuildException
);
marker_subclass!(
    AuthorizerBuildException,
    "Biscuit\\Exception\\AuthorizerBuildException",
    BuildException
);
marker_subclass!(
    ThirdPartyBlockAppendException,
    "Biscuit\\Exception\\ThirdPartyBlockAppendException",
    BuildException
);

#[php_class]
#[php(name = "Biscuit\\Exception\\BuilderStateException")]
#[php(extends(BiscuitException))]
#[derive(Default, Clone)]
pub struct BuilderStateException;

#[php_class]
#[php(name = "Biscuit\\Exception\\ThirdPartyException")]
#[php(extends(BiscuitException))]
#[derive(Default, Clone)]
pub struct ThirdPartyException;

impl From<BiscuitError> for PhpException {
    fn from(err: BiscuitError) -> Self {
        let message = collect_chain(&err);
        match err {
            BiscuitError::Key { kind, .. } => match kind {
                KeyKind::PublicKey => PhpException::from_class::<PublicKeyException>(message),
                KeyKind::PrivateKey => PhpException::from_class::<PrivateKeyException>(message),
            },
            BiscuitError::Datalog { kind, source } => {
                build_datalog_exception(kind, &*source, message)
            }
            BiscuitError::Format { kind, .. } => match kind {
                FormatKind::Base64 => PhpException::from_class::<Base64Exception>(message),
                FormatKind::Bytes => PhpException::from_class::<BytesException>(message),
                FormatKind::Signature => PhpException::from_class::<SignatureException>(message),
                FormatKind::Snapshot => PhpException::from_class::<SnapshotException>(message),
            },
            BiscuitError::Build { kind, .. } => match kind {
                BuildKind::Token => PhpException::from_class::<BiscuitBuildException>(message),
                BuildKind::Append => PhpException::from_class::<BlockAppendException>(message),
                BuildKind::Authorizer => {
                    PhpException::from_class::<AuthorizerBuildException>(message)
                }
                BuildKind::ThirdPartyAppend => {
                    PhpException::from_class::<ThirdPartyBlockAppendException>(message)
                }
            },
            BiscuitError::ThirdParty { .. } => {
                PhpException::from_class::<ThirdPartyException>(message)
            }
            BiscuitError::BuilderConsumed(_) => {
                PhpException::from_class::<BuilderStateException>(message)
            }
            BiscuitError::Authorization { source, policies } => {
                build_authorization_exception(&source, &policies, message)
            }
        }
    }
}

#[derive(Default)]
struct DatalogPayload {
    parse_errors: Option<Vec<ParseError>>,
    missing_parameters: Option<Vec<String>>,
    unused_parameters: Option<Vec<String>>,
}

fn build_datalog_exception(
    kind: DatalogKind,
    source: &(dyn std::error::Error + 'static),
    message: String,
) -> PhpException {
    let payload = classify_datalog(source);

    match kind {
        DatalogKind::Fact => into_php_exception(
            FactException::new(
                payload.parse_errors,
                payload.missing_parameters,
                payload.unused_parameters,
            ),
            message,
        ),
        DatalogKind::Rule => into_php_exception(
            RuleException::new(
                payload.parse_errors,
                payload.missing_parameters,
                payload.unused_parameters,
            ),
            message,
        ),
        DatalogKind::Check => into_php_exception(
            CheckException::new(
                payload.parse_errors,
                payload.missing_parameters,
                payload.unused_parameters,
            ),
            message,
        ),
        DatalogKind::Policy => into_php_exception(
            PolicyException::new(
                payload.parse_errors,
                payload.missing_parameters,
                payload.unused_parameters,
            ),
            message,
        ),
        DatalogKind::Term => into_php_exception(
            TermException::new(
                payload.parse_errors,
                payload.missing_parameters,
                payload.unused_parameters,
            ),
            message,
        ),
        DatalogKind::Scope => into_php_exception(
            ScopeException::new(
                payload.parse_errors,
                payload.missing_parameters,
                payload.unused_parameters,
            ),
            message,
        ),
    }
}

fn into_php_exception<T>(value: T, message: String) -> PhpException
where
    T: IntoZval + RegisteredClass,
{
    match ZendClassObject::new(value).into_zval(false) {
        Ok(mut zval) => {
            populate_exception_message(&mut zval, &message);
            PhpException::default(message).with_object(zval)
        }
        Err(_) => PhpException::from_class::<T>(message),
    }
}

fn classify_datalog(source: &(dyn std::error::Error + 'static)) -> DatalogPayload {
    match find_language_error(source) {
        Some(LanguageError::ParseError(parse_errors)) => DatalogPayload {
            parse_errors: Some(
                parse_errors
                    .errors
                    .iter()
                    .map(ParseError::from_upstream)
                    .collect(),
            ),
            ..DatalogPayload::default()
        },
        Some(LanguageError::Parameters {
            missing_parameters,
            unused_parameters,
        }) => DatalogPayload {
            missing_parameters: Some(missing_parameters.clone()),
            unused_parameters: Some(unused_parameters.clone()),
            ..DatalogPayload::default()
        },
        None => DatalogPayload::default(),
    }
}

fn find_language_error<'a>(
    err: &'a (dyn std::error::Error + 'static),
) -> Option<&'a LanguageError> {
    let mut current: Option<&'a (dyn std::error::Error + 'static)> = Some(err);
    while let Some(e) = current {
        if let Some(le) = e.downcast_ref::<LanguageError>() {
            return Some(le);
        }
        if let Some(Token::Language(le)) = e.downcast_ref::<Token>() {
            return Some(le);
        }
        current = e.source();
    }
    None
}

fn build_authorization_exception(
    error: &Token,
    policies: &[biscuit_auth::builder::Policy],
    message: String,
) -> PhpException {
    let (matched_policy, failed_checks) = match error {
        Token::FailedLogic(Logic::Unauthorized { policy, checks }) => (
            Some(MatchedPolicy::from_upstream(policy, policies)),
            checks.iter().map(FailedCheck::from_upstream).collect(),
        ),
        Token::FailedLogic(Logic::NoMatchingPolicy { checks }) => (
            None,
            checks.iter().map(FailedCheck::from_upstream).collect(),
        ),
        _ => (None, Vec::new()),
    };

    let payload = AuthorizationException::new(matched_policy, failed_checks);

    match ZendClassObject::new(payload).into_zval(false) {
        Ok(mut zval) => {
            populate_exception_message(&mut zval, &message);
            PhpException::default(message).with_object(zval)
        }
        Err(_) => PhpException::from_class::<AuthorizationException>(message),
    }
}
