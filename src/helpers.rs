use std::collections::HashMap;

use biscuit_auth::builder::Term;
use ext_php_rs::binary::Binary;
use ext_php_rs::class::RegisteredClass;
use ext_php_rs::convert::{FromZval, IntoZval};
use ext_php_rs::flags::DataType;
use ext_php_rs::prelude::*;
use ext_php_rs::types::Zval;
use ext_php_rs::zend::ClassEntry;

use crate::errors::{BiscuitError, DatalogKind, ResultExt, StaticError};
use crate::keys::PublicKey;

#[derive(Debug, ZvalConvert)]
pub enum MixedValue {
    Long(i64),
    Bool(bool),
    ParsedStr(String),
    Bytes(Bytes),
    Array(Vec<MixedValue>),
    Date(Timestamp),
    None,
}

#[php_class]
#[php(name = "Biscuit\\Auth\\Bytes")]
#[derive(Debug, Clone)]
pub struct Bytes(Vec<u8>);

#[php_impl]
impl Bytes {
    pub fn __construct(data: Binary<u8>) -> Self {
        Self(data.into())
    }

    pub fn __to_string(&self) -> Binary<u8> {
        Binary::new(self.0.clone())
    }
}

impl FromZval<'_> for Bytes {
    const TYPE: DataType = DataType::object(Self::CLASS_NAME);

    fn from_zval(zval: &Zval) -> Option<Self> {
        <&Self>::from_zval(zval).cloned()
    }
}

#[derive(Debug)]
pub struct Timestamp(i64);

impl FromZval<'_> for Timestamp {
    const TYPE: DataType = DataType::object("DateTimeInterface");

    fn from_zval(zval: &Zval) -> Option<Self> {
        let date = zval.object()?;
        if !date.instance_of(ClassEntry::try_find("DateTimeInterface")?) {
            return None;
        }
        date.try_call_method("getTimestamp", vec![])
            .ok()?
            .long()
            .map(Self)
    }
}

impl IntoZval for Timestamp {
    const TYPE: DataType = DataType::object("DateTimeImmutable");
    const NULLABLE: bool = false;

    fn set_zval(self, zv: &mut Zval, persistent: bool) -> ext_php_rs::error::Result<()> {
        ZendCallable::try_from_name("date_create_immutable")?
            .try_call(vec![&format!("@{}", self.0)])?
            .set_zval(zv, persistent)
    }
}

pub fn mixed_value_to_term(value: &MixedValue) -> PhpResult<Term> {
    match value {
        MixedValue::Long(v) => Ok(Term::Integer(*v)),
        MixedValue::Bool(b) => Ok(Term::Bool(*b)),
        MixedValue::ParsedStr(s) => Ok(Term::Str(s.clone())),
        MixedValue::Bytes(Bytes(b)) => Ok(Term::Bytes(b.clone())),
        MixedValue::Array(arr) => {
            let terms: Result<Vec<_>, _> = arr.iter().map(mixed_value_to_term).collect();
            let term_set: std::collections::BTreeSet<_> = terms?.into_iter().collect();
            Ok(Term::Set(term_set))
        }
        MixedValue::Date(Timestamp(ts)) => Ok(Term::Date(
            u64::try_from(*ts)
                .map_err(|_| StaticError("date out of range"))
                .datalog(DatalogKind::Term)?,
        )),
        MixedValue::None => {
            Err::<_, StaticError>(StaticError("unexpected value")).datalog(DatalogKind::Term)?
        }
    }
}

pub fn term_to_mixed_value(term: &Term) -> PhpResult<MixedValue> {
    match term {
        Term::Integer(v) => Ok(MixedValue::Long(*v)),
        Term::Bool(b) => Ok(MixedValue::Bool(*b)),
        Term::Str(s) => Ok(MixedValue::ParsedStr(s.clone())),
        Term::Bytes(b) => Ok(MixedValue::Bytes(Bytes(b.clone()))),
        Term::Set(set) => Ok(MixedValue::Array(
            set.iter()
                .map(term_to_mixed_value)
                .collect::<PhpResult<_>>()?,
        )),
        Term::Date(secs) => Ok(MixedValue::Date(Timestamp(
            i64::try_from(*secs)
                .map_err(|_| StaticError("date out of range"))
                .datalog(DatalogKind::Term)?,
        ))),
        Term::Null | Term::Array(_) | Term::Map(_) | Term::Variable(_) | Term::Parameter(_) => {
            Err::<_, StaticError>(StaticError("unsupported term")).datalog(DatalogKind::Term)?
        }
    }
}

pub fn take_builder<T>(opt: &mut Option<T>) -> PhpResult<T> {
    opt.take()
        .ok_or_else(|| BiscuitError::BuilderConsumed("builder has already been consumed").into())
}

pub fn get_builder<T>(opt: &Option<T>) -> PhpResult<&T> {
    opt.as_ref()
        .ok_or_else(|| BiscuitError::BuilderConsumed("builder has already been consumed").into())
}

pub(crate) fn collect_term_params(
    params: Option<HashMap<String, MixedValue>>,
) -> PhpResult<HashMap<String, biscuit_auth::builder::Term>> {
    match params {
        Some(p) => p
            .iter()
            .map(|(k, v)| mixed_value_to_term(v).map(|term| (k.clone(), term)))
            .collect(),
        None => Ok(HashMap::new()),
    }
}

pub(crate) fn collect_scope_params(
    scope_params: Option<HashMap<String, &PublicKey>>,
) -> HashMap<String, biscuit_auth::PublicKey> {
    match scope_params {
        Some(sp) => sp.iter().map(|(k, v)| (k.clone(), v.0)).collect(),
        None => HashMap::new(),
    }
}
