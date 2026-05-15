use ext_php_rs::prelude::*;

use crate::errors::{BiscuitError, DatalogKind, ResultExt, StaticError};

#[derive(Debug, ZvalConvert)]
pub enum MixedValue {
    Long(i64),
    Bool(bool),
    ParsedStr(String),
    Bytes(Vec<u8>),
    Array(Vec<MixedValue>),
    None,
}

pub fn mixed_value_to_term(value: &MixedValue) -> PhpResult<biscuit_auth::builder::Term> {
    match value {
        MixedValue::Long(v) => Ok(biscuit_auth::builder::Term::Integer(*v)),
        MixedValue::Bool(b) => Ok(biscuit_auth::builder::Term::Bool(*b)),
        MixedValue::ParsedStr(s) => Ok(biscuit_auth::builder::Term::Str(s.clone())),
        MixedValue::Bytes(b) => Ok(biscuit_auth::builder::Term::Bytes(b.clone())),
        MixedValue::Array(arr) => {
            let terms: Result<Vec<_>, _> = arr.iter().map(mixed_value_to_term).collect();
            let term_set: std::collections::BTreeSet<_> = terms?.into_iter().collect();
            Ok(biscuit_auth::builder::Term::Set(term_set))
        }
        MixedValue::None => {
            Err::<_, StaticError>(StaticError("unexpected value")).datalog(DatalogKind::Term)?
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
