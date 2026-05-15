use biscuit_auth::error::{
    FailedAuthorizerCheck, FailedBlockCheck, FailedCheck as UpstreamFailedCheck,
    MatchedPolicy as UpstreamMatchedPolicy,
};
use ext_php_rs::prelude::*;

use crate::errors::BiscuitException;

#[php_class]
#[php(name = "Biscuit\\Auth\\MatchedPolicy")]
#[derive(Debug, Clone)]
pub struct MatchedPolicy {
    kind: PolicyKind,
    policy_id: i64,
    code: Option<String>,
}

#[derive(Debug, Clone, Copy)]
enum PolicyKind {
    Allow,
    Deny,
}

impl PolicyKind {
    fn as_str(self) -> &'static str {
        match self {
            PolicyKind::Allow => "allow",
            PolicyKind::Deny => "deny",
        }
    }
}

impl MatchedPolicy {
    pub(crate) fn from_upstream(
        policy: &UpstreamMatchedPolicy,
        policies: &[biscuit_auth::builder::Policy],
    ) -> Self {
        let (kind, idx) = match policy {
            UpstreamMatchedPolicy::Allow(i) => (PolicyKind::Allow, *i),
            UpstreamMatchedPolicy::Deny(i) => (PolicyKind::Deny, *i),
        };
        let code = policies.get(idx).map(ToString::to_string);
        Self {
            kind,
            policy_id: idx as i64,
            code,
        }
    }

    pub(crate) fn allow(policy_id: usize, code: Option<String>) -> Self {
        Self {
            kind: PolicyKind::Allow,
            policy_id: policy_id as i64,
            code,
        }
    }
}

#[php_impl]
impl MatchedPolicy {
    pub fn get_kind(&self) -> String {
        self.kind.as_str().to_string()
    }

    pub fn get_policy_id(&self) -> i64 {
        self.policy_id
    }

    pub fn get_code(&self) -> Option<String> {
        self.code.clone()
    }
}

#[php_class]
#[php(name = "Biscuit\\Auth\\FailedCheck")]
#[derive(Debug, Clone)]
pub struct FailedCheck {
    origin: CheckOrigin,
    block_id: Option<i64>,
    check_id: i64,
    rule: String,
}

#[derive(Debug, Clone, Copy)]
enum CheckOrigin {
    Block,
    Authorizer,
}

impl CheckOrigin {
    fn as_str(self) -> &'static str {
        match self {
            CheckOrigin::Block => "block",
            CheckOrigin::Authorizer => "authorizer",
        }
    }
}

impl FailedCheck {
    pub(crate) fn from_upstream(check: &UpstreamFailedCheck) -> Self {
        match check {
            UpstreamFailedCheck::Block(FailedBlockCheck {
                block_id,
                check_id,
                rule,
            }) => Self {
                origin: CheckOrigin::Block,
                block_id: Some(i64::from(*block_id)),
                check_id: i64::from(*check_id),
                rule: rule.clone(),
            },
            UpstreamFailedCheck::Authorizer(FailedAuthorizerCheck { check_id, rule }) => Self {
                origin: CheckOrigin::Authorizer,
                block_id: None,
                check_id: i64::from(*check_id),
                rule: rule.clone(),
            },
        }
    }
}

#[php_impl]
impl FailedCheck {
    pub fn get_origin(&self) -> String {
        self.origin.as_str().to_string()
    }

    pub fn get_block_id(&self) -> Option<i64> {
        self.block_id
    }

    pub fn get_check_id(&self) -> i64 {
        self.check_id
    }

    pub fn get_rule(&self) -> String {
        self.rule.clone()
    }
}

#[php_class]
#[php(name = "Biscuit\\Exception\\AuthorizationException")]
#[php(extends(BiscuitException))]
#[derive(Debug, Clone, Default)]
pub struct AuthorizationException {
    matched_policy: Option<MatchedPolicy>,
    failed_checks: Vec<FailedCheck>,
}

impl AuthorizationException {
    pub(crate) fn new(
        matched_policy: Option<MatchedPolicy>,
        failed_checks: Vec<FailedCheck>,
    ) -> Self {
        Self {
            matched_policy,
            failed_checks,
        }
    }
}

#[php_impl]
impl AuthorizationException {
    pub fn get_matched_policy(&self) -> Option<MatchedPolicy> {
        self.matched_policy.clone()
    }

    pub fn get_failed_checks(&self) -> Vec<FailedCheck> {
        self.failed_checks.clone()
    }
}
