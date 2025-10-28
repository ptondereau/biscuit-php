PHP_ARG_ENABLE([debug], [Build the extension in debug mode], [  --enable-debug     Build the extension in debug mode], no, no)

AC_PATH_PROG([CARGO], [cargo])
if test -z "$CARGO"; then
  AC_MSG_ERROR([Cargo not found! Install Rust via https://rustup.rs])
fi

AC_PATH_PROG([RUSTC], [rustc])
if test -z "$RUSTC"; then
  AC_MSG_ERROR([Rust compiler (rustc) not found! Install Rust via https://rustup.rs])
fi

CARGO_FLAGS="--release"
CARGO_BUILD_DIR="target/release"

if test "$PHP_DEBUG" == "yes"; then
  CARGO_FLAGS=""
  CARGO_BUILD_DIR="target/debug"
fi
cat >>Makefile.objects<< EOF
EXT_NAME = biscuit
all: cargo_build

clean: cargo_clean

cargo_build:
	@echo "Building the Rust extension"
	PHP_CONFIG=$(which $PHP_PHP_CONFIG) PHP=$PHP_EXECUTABLE cargo build $CARGO_FLAGS
	cp $CARGO_BUILD_DIR/AS_ESCAPE([lib$(EXT_NAME)]).so AS_ESCAPE([$(phplibdir)])/AS_ESCAPE([$(EXT_NAME)]).so

cargo_clean:
	@echo "Cleaning the Rust extension"
	cargo clean

.PHONY: cargo_build cargo_clean
EOF

AC_CONFIG_LINKS([
  Cargo.toml:../Cargo.toml
  src:../src
  Cargo.lock:../Cargo.lock
])