<?hh
// Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved.

<<__EnumClass>>
class EC {
    const X = new stdclass;
}

<<__EnumClass>>
class C {
    const X = EC::X;
}

<<__EntryPoint>>
function enum_class04() {
  var_dump(C::X);
}
