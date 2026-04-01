use ext_php_rs::prelude::*;
use ext_php_rs::zend::ce;

#[php_class]
#[php(name = "Biscuit\\Exception\\InvalidPrivateKey")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default, Clone)]
pub struct InvalidPrivateKey;

#[php_class]
#[php(name = "Biscuit\\Exception\\InvalidPublicKey")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default, Clone)]
pub struct InvalidPublicKey;

#[php_class]
#[php(name = "Biscuit\\Exception\\InvalidCheck")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default, Clone)]
pub struct InvalidCheck;

#[php_class]
#[php(name = "Biscuit\\Exception\\InvalidPolicy")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default, Clone)]
pub struct InvalidPolicy;

#[php_class]
#[php(name = "Biscuit\\Exception\\InvalidFact")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default, Clone)]
pub struct InvalidFact;

#[php_class]
#[php(name = "Biscuit\\Exception\\InvalidRule")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default, Clone)]
pub struct InvalidRule;

#[php_class]
#[php(name = "Biscuit\\Exception\\InvalidTerm")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default, Clone)]
pub struct InvalidTerm;

#[php_class]
#[php(name = "Biscuit\\Exception\\ThirdPartyRequestError")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default, Clone)]
pub struct ThirdPartyRequestError;

#[php_class]
#[php(name = "Biscuit\\Exception\\AuthorizerError")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default, Clone)]
pub struct AuthorizerError;

#[php_class]
#[php(name = "Biscuit\\Exception\\BuilderConsumed")]
#[php(extends(ce = ce::exception, stub = "\\Exception"))]
#[derive(Default, Clone)]
pub struct BuilderConsumed;
