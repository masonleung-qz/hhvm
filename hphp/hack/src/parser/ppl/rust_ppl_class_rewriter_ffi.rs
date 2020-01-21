// Copyright (c) Facebook, Inc. and its affiliates.
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the "hack" directory of this source tree.

use std::rc::Rc;

use ocamlrep::OcamlRep;
use parser::{
    parser::Parser, parser_env::ParserEnv, smart_constructors::NoState,
    smart_constructors::WithKind, source_text::SourceText,
};
use positioned_smart_constructors::PositionedSmartConstructors;
use rust_editable_positioned_syntax::{EditablePositionedSyntax, EditablePositionedSyntaxTrait};
use rust_ppl_class_rewriter::PplClassRewriter;
use rust_to_ocaml::{SerializationContext, ToOcaml};

type PositionedSyntaxParser<'a> = Parser<'a, WithKind<PositionedSmartConstructors>, NoState>;

extern "C" {
    fn ocamlpool_enter();
    fn ocamlpool_leave();
}

#[no_mangle]
pub extern "C" fn parse_and_rewrite_ppl_classes(ocaml_source_text: usize) -> usize {
    let source_text = unsafe { SourceText::from_ocaml(ocaml_source_text).unwrap() };

    let env = ParserEnv {
        is_experimental_mode: false,
        hhvm_compat_mode: false,
        php5_compat_mode: false,
        codegen: false,
        allow_new_attribute_syntax: false,
        enable_xhp_class_modifier: false,
    };

    let mut parser = PositionedSyntaxParser::make(&source_text, env);
    let root = parser.parse_script(None);

    let context = SerializationContext::new(ocaml_source_text);

    let mut editable_root =
        EditablePositionedSyntax::from_positioned_syntax(&root, &Rc::new(source_text));
    PplClassRewriter::rewrite_ppl_classes(&mut editable_root);

    unsafe {
        ocamlpool_enter();
        let ocaml_root = editable_root.to_ocaml(&context);
        ocamlpool_leave();
        ocaml_root
    }
}
